<?php

namespace App\Livewire\App\AgenticTools;

use App\Models\Workspace;
use App\Models\CustomApiIntegration;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;

class CustomApiIntegrationForm extends Component
{
    #[Locked]
    public Workspace $workspace;

    #[Locked]
    public ?CustomApiIntegration $integration = null;

    public $step = 1;
    public $totalSteps = 4;

    #[Validate]
    public array $integrationForm = [
        'name' => '',
        'description' => '',
        'action_type' => 'get_data',
        'api_url' => '',
        'http_method' => 'GET',
        'timeout' => 10,
        'auth_type' => 'none',
        'auth_config' => [],
        'input_schema' => [],
        'trigger_keywords' => '',
        'ai_rules' => [
            'pre_submission_message' => '',
        ],
        'confirmation_message' => '',
        'success_response' => [
            'title' => '',
            'message' => '',
            'show_response_data' => false,
        ],
        'headers' => [],
        'is_active' => true,
    ];

    public function mount($uuid, $integrationId = null)
    {
        $this->workspace = Workspace::query()
            ->with('settings')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        if ($integrationId) {
            $this->integration = CustomApiIntegration::query()
                ->forWorkspace($this->workspace->id)
                ->findOrFail($integrationId);

            // Convert trigger_keywords array to string for the form
            $triggerKeywords = $this->integration->trigger_keywords;
            if (is_array($triggerKeywords)) {
                $triggerKeywords = implode(', ', $triggerKeywords);
            }

            $this->integrationForm = [
                'name' => $this->integration->name,
                'description' => $this->integration->description ?? '',
                'action_type' => $this->integration->action_type,
                'api_url' => $this->integration->api_url,
                'http_method' => $this->integration->http_method,
                'timeout' => $this->integration->timeout,
                'auth_type' => $this->integration->auth_type,
                'auth_config' => $this->integration->auth_config ?? [],
                'input_schema' => $this->integration->input_schema ?? [],
                'trigger_keywords' => $triggerKeywords ?? '',
                'ai_rules' => $this->integration->ai_rules ?? ['pre_submission_message' => ''],
                'confirmation_message' => $this->integration->confirmation_message ?? '',
                'success_response' => $this->integration->success_response ?? [
                    'title' => '',
                    'message' => '',
                    'show_response_data' => false,
                ],
                'headers' => $this->integration->headers ?? [],
                'is_active' => $this->integration->is_active,
            ];
        }
    }

    public function rules()
    {
        $rules = [
            'integrationForm.name' => 'required|string|max:255',
            'integrationForm.api_url' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Remove path parameters for URL validation
                    $urlForValidation = preg_replace('/\{[^}]+\}/', 'placeholder', $value);
                    
                    if (!filter_var($urlForValidation, FILTER_VALIDATE_URL)) {
                        $fail('The API URL must be a valid URL format. Dynamic parameters like {order_id} are allowed.');
                    }
                },
            ],
            'integrationForm.http_method' => 'required|in:GET,POST,PUT,PATCH,DELETE',
            'integrationForm.action_type' => 'required|in:get_data,submit_data',
            'integrationForm.auth_type' => 'required|in:none,bearer,api_key,basic,custom',
            'integrationForm.timeout' => 'required|integer|min:1|max:15',
        ];

        // Add auth-specific rules
        if ($this->integrationForm['auth_type'] === 'bearer') {
            $rules['integrationForm.auth_config.token'] = 'required|string';
        } elseif ($this->integrationForm['auth_type'] === 'api_key') {
            $rules['integrationForm.auth_config.key'] = 'required|string';
            $rules['integrationForm.auth_config.value'] = 'required|string';
            $rules['integrationForm.auth_config.location'] = 'required|in:header,query';
        } elseif ($this->integrationForm['auth_type'] === 'basic') {
            $rules['integrationForm.auth_config.username'] = 'required|string';
            $rules['integrationForm.auth_config.password'] = 'required|string';
        }

        return $rules;
    }

    public function nextStep()
    {
        $this->validateStep();
        
        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function goToStep($step)
    {
        if ($step >= 1 && $step <= $this->totalSteps) {
            $this->step = $step;
        }
    }

    public function validateStep()
    {
        switch ($this->step) {
            case 1:
                $this->validate([
                    'integrationForm.name' => 'required|string|max:255',
                    'integrationForm.action_type' => 'required|in:get_data,submit_data',
                ]);
                break;
            case 2:
                $this->validate([
                    'integrationForm.api_url' => [
                        'required',
                        'string',
                        function ($attribute, $value, $fail) {
                            // Remove path parameters for URL validation
                            $urlForValidation = preg_replace('/\{[^}]+\}/', 'placeholder', $value);
                            
                            if (!filter_var($urlForValidation, FILTER_VALIDATE_URL)) {
                                $fail('The API URL must be a valid URL format. Dynamic parameters like {order_id} are allowed.');
                            }
                        },
                    ],
                    'integrationForm.http_method' => 'required|in:GET,POST,PUT,PATCH,DELETE',
                    'integrationForm.timeout' => 'required|integer|min:1|max:15',
                ]);
                break;
            case 3:
                if ($this->integrationForm['auth_type'] !== 'none') {
                    $this->validate($this->getAuthRules());
                }
                break;
        }
    }

    private function getAuthRules()
    {
        $rules = [];
        
        if ($this->integrationForm['auth_type'] === 'bearer') {
            $rules['integrationForm.auth_config.token'] = 'required|string';
        } elseif ($this->integrationForm['auth_type'] === 'api_key') {
            $rules['integrationForm.auth_config.key'] = 'required|string';
            $rules['integrationForm.auth_config.value'] = 'required|string';
            $rules['integrationForm.auth_config.location'] = 'required|in:header,query';
        } elseif ($this->integrationForm['auth_type'] === 'basic') {
            $rules['integrationForm.auth_config.username'] = 'required|string';
            $rules['integrationForm.auth_config.password'] = 'required|string';
        } elseif ($this->integrationForm['auth_type'] === 'custom') {
            // Custom auth type doesn't require specific validation as it's flexible
            // Users can define custom headers as needed
        }

        return $rules;
    }

    public function addInputField()
    {
        $fieldName = 'field_' . (count($this->integrationForm['input_schema']) + 1);
        $this->integrationForm['input_schema'][$fieldName] = [
            'type' => 'text',
            'label' => ucwords(str_replace('_', ' ', $fieldName)),
            'placeholder' => '',
            'required' => false,
            'validation' => '',
        ];
    }

    public function removeInputField($fieldName)
    {
        unset($this->integrationForm['input_schema'][$fieldName]);
    }

    public function updateFieldName($oldName, $newName)
    {
        if ($oldName !== $newName && !isset($this->integrationForm['input_schema'][$newName])) {
            $this->integrationForm['input_schema'][$newName] = $this->integrationForm['input_schema'][$oldName];
            unset($this->integrationForm['input_schema'][$oldName]);
        }
    }

    public function testApi()
    {
        try {
            $this->validate();
            
            $testData = [];
            foreach ($this->integrationForm['input_schema'] as $fieldName => $fieldConfig) {
                $testData[$fieldName] = 'test_' . $fieldName;
            }

            $response = $this->makeApiRequest($testData);
            
            if ($response['success']) {
                Flux::toast(variant: 'success', text: 'API test successful! Response time: ' . $response['response_time'] . 'ms');
            } else {
                Flux::toast(variant: 'danger', text: 'API test failed: ' . ($response['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: 'API test failed: ' . $e->getMessage());
        }
    }

    public function saveIntegration()
    {
        try {
            $this->validate();

            // Prepare the data for saving
            $data = $this->integrationForm;
            
            // Convert trigger_keywords string to array
            if (isset($data['trigger_keywords']) && is_string($data['trigger_keywords'])) {
                $data['trigger_keywords'] = array_filter(
                    array_map('trim', explode(',', $data['trigger_keywords']))
                );
            }

            if ($this->integration) {
                $this->integration->update($data);
                $message = 'API integration updated successfully';
            } else {
                $this->integration = CustomApiIntegration::create([
                    ...$data,
                    'workspace_id' => $this->workspace->id,
                ]);
                $message = 'API integration created successfully';
            }

            Flux::toast(variant: 'success', text: $message);
            
            return $this->redirect(route('app.workspace.agentic-tools.custom-api-integrations', $this->workspace->uuid));
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Get the first validation error message
            $errors = $e->validator->errors()->all();
            $errorMessage = 'Validation failed: ' . implode(', ', $errors);
            Flux::toast(variant: 'danger', text: $errorMessage);
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: 'Error saving integration: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return $this->redirect(route('app.workspace.agentic-tools.custom-api-integrations', $this->workspace->uuid));
    }

    private function makeApiRequest($data)
    {
        $startTime = microtime(true);
        
        try {
            $headers = array_merge(
                ['Content-Type' => 'application/json'],
                $this->getAuthHeaders(),
                $this->integrationForm['headers'] ?? []
            );

            $queryParams = $this->getQueryParams($data);
            $url = $this->integrationForm['api_url'];
            
            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }

            $response = \Http::timeout($this->integrationForm['timeout'])
                ->withHeaders($headers);

            if ($this->integrationForm['http_method'] === 'GET') {
                $httpResponse = $response->get($url);
            } else {
                $httpResponse = $response->{strtolower($this->integrationForm['http_method'])}($url, $data);
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

    private function getAuthHeaders()
    {
        $headers = [];
        
        switch ($this->integrationForm['auth_type']) {
            case 'bearer':
                if (!empty($this->integrationForm['auth_config']['token'])) {
                    $headers['Authorization'] = 'Bearer ' . $this->integrationForm['auth_config']['token'];
                }
                break;
            case 'api_key':
                if ($this->integrationForm['auth_config']['location'] === 'header') {
                    $headers[$this->integrationForm['auth_config']['key']] = $this->integrationForm['auth_config']['value'];
                }
                break;
            case 'basic':
                if (!empty($this->integrationForm['auth_config']['username']) && !empty($this->integrationForm['auth_config']['password'])) {
                    $headers['Authorization'] = 'Basic ' . base64_encode(
                        $this->integrationForm['auth_config']['username'] . ':' . $this->integrationForm['auth_config']['password']
                    );
                }
                break;
        }

        return $headers;
    }

    private function getQueryParams($data)
    {
        $params = [];
        
        if ($this->integrationForm['auth_type'] === 'api_key' && 
            $this->integrationForm['auth_config']['location'] === 'query') {
            $params[$this->integrationForm['auth_config']['key']] = $this->integrationForm['auth_config']['value'];
        }

        if ($this->integrationForm['http_method'] === 'GET') {
            $params = array_merge($params, $data);
        }

        return $params;
    }

    public function render()
    {
        return view('livewire.app.agentic-tools.custom-api-integration-form')
            ->extends('layouts.app')
            ->section('main');
    }
}

