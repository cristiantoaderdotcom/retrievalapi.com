<?php

namespace App\Livewire\App\AgenticTools;

use App\Models\Workspace;
use App\Models\RefundRequest;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class RequestRefunds extends Component
{
    use WithPagination;

    #[Locked]
    public Workspace $workspace;

    public string $tab = 'configuration';

    // Refund requests management
    public $selectedRequest = null;
    public $showRequestModal = false;
    public $requestNotes = '';
    public $requestStatus = '';
    public $statusFilter = 'all';

    public array $agentic_refund_request = [
        'enabled' => true,
        'schema' => [
            'email' => [
                'type' => 'email',
                'required' => true,
                'label' => 'Email Address',
                'validation' => 'email',
                'placeholder' => 'customer@example.com'
            ],
            'sale_id' => [
                'type' => 'text',
                'required' => true,
                'label' => 'Sale ID',
                'validation' => 'regex:/^[a-zA-Z]{3}-\d{4}$/',
                'placeholder' => 'abc-1234'
            ],
            'reason' => [
                'type' => 'textarea',
                'required' => false,
                'label' => 'Refund Reason',
                'validation' => 'string',
                'placeholder' => 'Please describe why you are requesting a refund...'
            ]
        ],
        'ai_rules' => [
            'trigger_phrases' => 'refund, return, money back, billing issue, charge dispute, cancel order, get my money back',
            'validation_instructions' => 'Before processing a refund request, ensure all required fields are provided and validate the sale ID format (xxx-1234). If information is missing or incorrect, politely ask the user to provide the correct details.',
            'pre_submission_message' => 'I understand you would like to request a refund. Let me collect the necessary information to process your request.',
            'collection_prompts' => [
                'email' => 'Could you please provide the email address associated with your purchase?',
                'sale_id' => 'What is your sale ID? It should be in the format xxx-1234 (for example: abc-5678).',
                'reason' => 'Could you please tell me the reason for your refund request? This will help our team process it faster.'
            ]
        ],
        'success_response' => [
            'title' => 'Refund Request Submitted Successfully',
            'message' => 'Your refund request has been submitted and our support team will review it within 24-48 hours. You will receive a confirmation email shortly.',
            'show_details' => true,
            'additional_info' => 'If you have any questions about your refund request, please don\'t hesitate to contact our support team.'
        ]
    ];
 

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->with('settings')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        $this->agentic_refund_request = $this->workspace->setting('agentic_refund_request', $this->agentic_refund_request);
    }

    public function save()
    {
        // Validate the configuration
        $this->validateConfiguration();

        $this->workspace->settings()->updateOrCreate(
            ['key' => 'agentic_refund_request'],
            ['value' => $this->agentic_refund_request]
        );

        Flux::toast(variant: 'success', text: 'Refund request configuration updated successfully');
    }

    public function addField()
    {
        $fieldName = 'custom_field_' . time();
        $this->agentic_refund_request['schema'][$fieldName] = [
            'type' => 'text',
            'required' => false,
            'label' => 'New Field',
            'validation' => 'string',
            'placeholder' => ''
        ];
        
        // Add a default collection prompt for the new field
        $this->agentic_refund_request['ai_rules']['collection_prompts'][$fieldName] = 'Could you please provide the new field information?';
    }

    public function removeField($fieldName)
    {
        // Allow removal of any field, including core fields
        unset($this->agentic_refund_request['schema'][$fieldName]);
        
        // Also remove the collection prompt for this field
        if (isset($this->agentic_refund_request['ai_rules']['collection_prompts'][$fieldName])) {
            unset($this->agentic_refund_request['ai_rules']['collection_prompts'][$fieldName]);
        }
    }

    public function toggleTool()
    {
        $this->agentic_refund_request['enabled'] = !$this->agentic_refund_request['enabled'];
    }

    public function resetToDefaults()
    {
        $this->agentic_refund_request = [
            'enabled' => true,
            'schema' => [
                'email' => [
                    'type' => 'email',
                    'required' => true,
                    'label' => 'Email Address',
                    'validation' => 'email',
                    'placeholder' => 'customer@example.com'
                ],
                'sale_id' => [
                    'type' => 'text',
                    'required' => true,
                    'label' => 'Sale ID',
                    'validation' => 'regex:/^[a-zA-Z]{3}-\d{4}$/',
                    'placeholder' => 'abc-1234'
                ],
                'reason' => [
                    'type' => 'textarea',
                    'required' => false,
                    'label' => 'Refund Reason',
                    'validation' => 'string',
                    'placeholder' => 'Please describe why you are requesting a refund...'
                ]
            ],
            'ai_rules' => [
                'trigger_phrases' => 'refund, return, money back, billing issue, charge dispute, cancel order, get my money back',
                'validation_instructions' => 'Before processing a refund request, ensure all required fields are provided and validate the sale ID format (xxx-1234). If information is missing or incorrect, politely ask the user to provide the correct details.',
                'pre_submission_message' => 'I understand you would like to request a refund. Let me collect the necessary information to process your request.',
                'collection_prompts' => [
                    'email' => 'Could you please provide the email address associated with your purchase?',
                    'sale_id' => 'What is your sale ID? It should be in the format xxx-1234 (for example: abc-5678).',
                    'reason' => 'Could you please tell me the reason for your refund request? This will help our team process it faster.'
                ]
            ],
            'success_response' => [
                'title' => 'Refund Request Submitted Successfully',
                'message' => 'Your refund request has been submitted and our support team will review it within 24-48 hours. You will receive a confirmation email shortly.',
                'show_details' => true,
                'additional_info' => 'If you have any questions about your refund request, please don\'t hesitate to contact our support team.'
            ]
        ];

        Flux::toast(variant: 'info', text: 'Configuration reset to defaults');
    }

    // Refund request management methods
    public function getRefundRequestsProperty()
    {
        $query = RefundRequest::query()
            ->whereHas('conversation', function ($q) {
                $q->where('workspace_id', $this->workspace->id);
            })
            ->with('conversation')
            ->orderBy('created_at', 'desc');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->paginate(10);
    }

    public function viewRequest($requestId)
    {
        $this->selectedRequest = RefundRequest::with('conversation')->findOrFail($requestId);
        $this->requestNotes = $this->selectedRequest->notes ?? '';
        $this->requestStatus = $this->selectedRequest->status;
        $this->showRequestModal = true;
    }

    public function updateRequestStatus()
    {
        if (!$this->selectedRequest) {
            return;
        }

        $this->selectedRequest->update([
            'status' => $this->requestStatus,
            'notes' => $this->requestNotes,
            'processed_at' => now(),
        ]);

        $this->showRequestModal = false;
        $this->selectedRequest = null;
        $this->resetPage();

        Flux::toast(variant: 'success', text: 'Refund request updated successfully');
    }

    public function closeModal()
    {
        $this->showRequestModal = false;
        $this->selectedRequest = null;
        $this->requestNotes = '';
        $this->requestStatus = '';
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    private function validateConfiguration()
    {
        // Ensure at least one field exists
        if (empty($this->agentic_refund_request['schema'])) {
            throw new \Exception("At least one field is required in the schema");
        }

        // Validate field types
        $validTypes = ['text', 'email', 'textarea', 'number'];
        foreach ($this->agentic_refund_request['schema'] as $fieldName => $field) {
            if (!in_array($field['type'], $validTypes)) {
                throw new \Exception("Invalid field type for '{$fieldName}'. Must be one of: " . implode(', ', $validTypes));
            }
        }
    }

    public function render()
    {
        return view('livewire.app.agentic-tools.request-refunds')
            ->extends('layouts.app')
            ->section('main');
    }
}
