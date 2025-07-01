<?php

namespace App\Livewire\App\AgenticTools;

use App\Models\Workspace;
use App\Models\BookingIntegration;
use App\Models\BookingRequest;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class Booking extends Component
{
    use WithPagination;

    #[Locked]
    public Workspace $workspace;

    public string $tab = 'integrations';

    // Integration management
    public $selectedIntegration = null;
    public $showIntegrationModal = false;
    public $showCreateModal = false;
    public $integrationForm = [];
    public $selectedPlatform = '';

    // Request management
    public $selectedRequest = null;
    public $showRequestModal = false;
    public $requestNotes = '';
    public $requestStatus = '';
    public $statusFilter = 'all';
    public $integrationFilter = 'all';

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->with('settings')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        $this->resetIntegrationForm();
    }

    // Integration Management Methods
    public function getIntegrationsProperty()
    {
        return BookingIntegration::query()
            ->forWorkspace($this->workspace->id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function createIntegration()
    {
        $this->resetIntegrationForm();
        $this->showCreateModal = true;
    }

    public function editIntegration($integrationId)
    {
        $integration = BookingIntegration::findOrFail($integrationId);
        $this->selectedIntegration = $integration;
        
        $this->integrationForm = [
            'platform' => $integration->platform,
            'name' => $integration->name,
            'status' => $integration->status,
            'configuration' => $integration->configuration ?? [],
            'trigger_keywords' => $integration->trigger_keywords_string,
            'confirmation_message' => $integration->confirmation_message,
            'ai_instructions' => $integration->ai_instructions,
            'is_default' => $integration->is_default,
        ];
        
        $this->selectedPlatform = $integration->platform;
        $this->showIntegrationModal = true;
    }

    public function saveIntegration()
    {
        $this->validateIntegrationForm();

        $data = [
            'workspace_id' => $this->workspace->id,
            'platform' => $this->integrationForm['platform'],
            'name' => $this->integrationForm['name'],
            'status' => $this->integrationForm['status'] ?? BookingIntegration::STATUS_ACTIVE,
            'configuration' => $this->integrationForm['configuration'] ?? [],
            'trigger_keywords' => $this->parseKeywords($this->integrationForm['trigger_keywords'] ?? ''),
            'confirmation_message' => $this->integrationForm['confirmation_message'],
            'ai_instructions' => $this->integrationForm['ai_instructions'],
            'is_default' => $this->integrationForm['is_default'] ?? false,
        ];

        // If setting as default, remove default from others
        if ($data['is_default']) {
            BookingIntegration::forWorkspace($this->workspace->id)
                ->update(['is_default' => false]);
        }

        if ($this->selectedIntegration) {
            $this->selectedIntegration->update($data);
            $message = 'Booking integration updated successfully';
        } else {
            BookingIntegration::create($data);
            $message = 'Booking integration created successfully';
        }

        $this->closeModals();
        Flux::toast(variant: 'success', text: $message);
    }

    public function deleteIntegration($integrationId)
    {
        $integration = BookingIntegration::findOrFail($integrationId);
        $integration->delete();
        
        Flux::toast(variant: 'success', text: 'Booking integration deleted successfully');
    }

    public function toggleIntegrationStatus($integrationId)
    {
        $integration = BookingIntegration::findOrFail($integrationId);
        $newStatus = $integration->status === BookingIntegration::STATUS_ACTIVE 
            ? BookingIntegration::STATUS_INACTIVE 
            : BookingIntegration::STATUS_ACTIVE;
        
        $integration->update(['status' => $newStatus]);
        
        Flux::toast(variant: 'success', text: 'Integration status updated');
    }

    public function setAsDefault($integrationId)
    {
        // Remove default from all integrations
        BookingIntegration::forWorkspace($this->workspace->id)
            ->update(['is_default' => false]);
        
        // Set the selected one as default
        BookingIntegration::findOrFail($integrationId)
            ->update(['is_default' => true]);
        
        Flux::toast(variant: 'success', text: 'Default integration updated');
    }

    // Request Management Methods
    public function getBookingRequestsProperty()
    {
        $query = BookingRequest::query()
            ->forWorkspace($this->workspace->id)
            ->with(['bookingIntegration', 'conversation'])
            ->recent();

        if ($this->statusFilter !== 'all') {
            $query->byStatus($this->statusFilter);
        }

        if ($this->integrationFilter !== 'all') {
            $query->where('booking_integration_id', $this->integrationFilter);
        }

        return $query->paginate(10);
    }

    public function getIntegrationsForFilterProperty()
    {
        return BookingIntegration::forWorkspace($this->workspace->id)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function viewRequest($requestId)
    {
        $this->selectedRequest = BookingRequest::with(['bookingIntegration', 'conversation'])
            ->findOrFail($requestId);
        $this->requestNotes = $this->selectedRequest->notes ?? '';
        $this->requestStatus = $this->selectedRequest->status;
        $this->showRequestModal = true;
    }

    public function updateRequestStatus()
    {
        if (!$this->selectedRequest) {
            return;
        }

        $updateData = [
            'status' => $this->requestStatus,
            'notes' => $this->requestNotes,
        ];

        if ($this->requestStatus === BookingRequest::STATUS_COMPLETED && !$this->selectedRequest->completed_at) {
            $updateData['completed_at'] = now();
        }

        $this->selectedRequest->update($updateData);

        $this->showRequestModal = false;
        $this->selectedRequest = null;
        $this->resetPage();

        Flux::toast(variant: 'success', text: 'Booking request updated successfully');
    }

    // Helper Methods
    private function resetIntegrationForm()
    {
        $this->integrationForm = [
            'platform' => '',
            'name' => '',
            'status' => BookingIntegration::STATUS_ACTIVE,
            'configuration' => [],
            'trigger_keywords' => 'book appointment, schedule meeting, book a call, set up meeting, schedule consultation',
            'confirmation_message' => 'I\'d be happy to help you schedule an appointment. Let me direct you to our booking system.',
            'ai_instructions' => 'When a user wants to book an appointment, provide them with the booking link and any relevant information about the scheduling process.',
            'is_default' => false,
        ];
        $this->selectedPlatform = '';
    }

    private function validateIntegrationForm()
    {
        $rules = [
            'integrationForm.platform' => 'required|string',
            'integrationForm.name' => 'required|string|max:255',
            'integrationForm.trigger_keywords' => 'required|string',
            'integrationForm.confirmation_message' => 'required|string',
        ];

        // Add platform-specific validation
        if ($this->selectedPlatform) {
            $platforms = BookingIntegration::getAvailablePlatforms();
            $platformConfig = $platforms[$this->selectedPlatform] ?? [];
            
            foreach ($platformConfig['fields'] ?? [] as $fieldKey => $fieldConfig) {
                if ($fieldConfig['required']) {
                    $rules["integrationForm.configuration.{$fieldKey}"] = 'required';
                }
            }
        }

        $this->validate($rules);
    }

    private function parseKeywords($keywordsString)
    {
        return array_map('trim', explode(',', $keywordsString));
    }

    public function updatedSelectedPlatform()
    {
        if ($this->selectedPlatform) {
            $platforms = BookingIntegration::getAvailablePlatforms();
            $platformConfig = $platforms[$this->selectedPlatform] ?? [];
            
            // Initialize configuration with empty values for the platform fields
            $configuration = [];
            foreach ($platformConfig['fields'] ?? [] as $fieldKey => $fieldConfig) {
                $configuration[$fieldKey] = '';
            }
            
            $this->integrationForm['configuration'] = $configuration;
            $this->integrationForm['platform'] = $this->selectedPlatform;
            
            // Set default name if not set
            if (empty($this->integrationForm['name'])) {
                $this->integrationForm['name'] = $platformConfig['label'] ?? 'New Integration';
            }
        }
    }

    public function closeModals()
    {
        $this->showIntegrationModal = false;
        $this->showCreateModal = false;
        $this->showRequestModal = false;
        $this->selectedIntegration = null;
        $this->selectedRequest = null;
        $this->requestNotes = '';
        $this->requestStatus = '';
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedIntegrationFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.app.agentic-tools.booking')
            ->extends('layouts.app')
            ->section('main');
    }
} 