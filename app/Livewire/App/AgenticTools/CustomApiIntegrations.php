<?php

namespace App\Livewire\App\AgenticTools;

use App\Models\Workspace;
use App\Models\CustomApiIntegration;
use App\Models\CustomApiRequest;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class CustomApiIntegrations extends Component
{
    use WithPagination;

    #[Locked]
    public Workspace $workspace;

    public string $tab = 'integrations';

    // Request management
    public $selectedRequest = null;
    public $showRequestModal = false;
    public $statusFilter = 'all';
    public $integrationFilter = 'all';

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->with('settings')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();
    }

    // Integration Management Methods
    public function getIntegrationsProperty()
    {
        return CustomApiIntegration::query()
            ->forWorkspace($this->workspace->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createIntegration()
    {
        return $this->redirect(route('app.workspace.agentic-tools.custom-api-integrations.create', $this->workspace->uuid));
    }

    public function editIntegration($integrationId)
    {
        return $this->redirect(route('app.workspace.agentic-tools.custom-api-integrations.edit', [
            'uuid' => $this->workspace->uuid,
            'integrationId' => $integrationId
        ]));
    }

    public function deleteIntegration($integrationId)
    {
        $integration = CustomApiIntegration::findOrFail($integrationId);
        $integration->delete();
        
        Flux::toast(variant: 'success', text: 'API integration deleted successfully');
    }

    public function toggleIntegrationStatus($integrationId)
    {
        $integration = CustomApiIntegration::findOrFail($integrationId);
        $integration->update(['is_active' => !$integration->is_active]);
        
        $status = $integration->is_active ? 'activated' : 'deactivated';
        Flux::toast(variant: 'success', text: "Integration {$status} successfully");
    }

    public function testIntegration($integrationId)
    {
        $integration = CustomApiIntegration::findOrFail($integrationId);
        
        // Create a test request with sample data
        $testData = [];
        foreach ($integration->input_schema as $fieldName => $fieldConfig) {
            $testData[$fieldName] = 'test_' . $fieldName;
        }

        try {
            $response = $this->makeApiRequest($integration, $testData);
            
            if ($response['success']) {
                Flux::toast(variant: 'success', text: 'API test successful! Response time: ' . $response['response_time'] . 'ms');
            } else {
                Flux::toast(variant: 'danger', text: 'API test failed: ' . ($response['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: 'API test failed: ' . $e->getMessage());
        }
    }

    // Request Management Methods
    public function getApiRequestsProperty()
    {
        $query = CustomApiRequest::query()
            ->forWorkspace($this->workspace->id)
            ->with(['customApiIntegration', 'conversation'])
            ->recent();

        if ($this->statusFilter !== 'all') {
            $query->byStatus($this->statusFilter);
        }

        if ($this->integrationFilter !== 'all') {
            $query->byIntegration($this->integrationFilter);
        }

        return $query->paginate(10);
    }

    public function getIntegrationsForFilterProperty()
    {
        return CustomApiIntegration::forWorkspace($this->workspace->id)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function viewRequest($requestId)
    {
        $this->selectedRequest = CustomApiRequest::with(['customApiIntegration', 'conversation'])
            ->findOrFail($requestId);
        $this->showRequestModal = true;
    }

    public function closeModals()
    {
        $this->showRequestModal = false;
        $this->selectedRequest = null;
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedIntegrationFilter()
    {
        $this->resetPage();
    }

    private function makeApiRequest($integration, $data)
    {
        $startTime = microtime(true);
        
        try {
            $headers = array_merge(
                ['Content-Type' => 'application/json'],
                $integration->getAuthHeaders(),
                $integration->headers ?? []
            );

            $queryParams = $integration->getQueryParams($data);
            $url = $integration->api_url;
            
            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }

            $response = \Http::timeout($integration->timeout)
                ->withHeaders($headers);

            if ($integration->http_method === 'GET') {
                $httpResponse = $response->get($url);
            } else {
                $httpResponse = $response->{strtolower($integration->http_method)}($url, $data);
            }

            $responseTime = round((microtime(true) - $startTime) * 1000);

            return [
                'success' => $httpResponse->successful(),
                'data' => $httpResponse->json(),
                'status_code' => $httpResponse->status(),
                'response_time' => $responseTime,
                'raw_response' => $httpResponse->body(),
                'error' => $httpResponse->successful() ? null : $httpResponse->body(),
            ];

        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000);
            
            return [
                'success' => false,
                'data' => null,
                'status_code' => 0,
                'response_time' => $responseTime,
                'raw_response' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function render()
    {
        return view('livewire.app.agentic-tools.custom-api-integrations')
            ->extends('layouts.app')
            ->section('main');
    }
}
