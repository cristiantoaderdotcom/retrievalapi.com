<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <flux:button 
                        variant="ghost" 
                        icon="arrow-left" 
                        wire:click="cancel"
                        class="p-2 rounded-lg hover:bg-gray-100">
                        Back
                    </flux:button>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ $integration ? 'Edit Integration' : 'Create New Integration' }}
                        </h1>
                        <p class="mt-1 text-lg text-gray-600">
                            {{ $integration ? 'Modify your existing API integration settings' : 'Connect your AI assistant with external APIs' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @if($integration)
                        <flux:button variant="outline" wire:click="testApi" icon="beaker" size="sm">
                            Test API
                        </flux:button>
                    @endif
                    <flux:button variant="primary" wire:click="saveIntegration" icon="check" size="sm">
                        {{ $integration ? 'Update' : 'Create' }} Integration
                    </flux:button>
                </div>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Progress Steps -->
            <div class="bg-white rounded-lg border shadow-sm p-6">
                <div class="flex items-center justify-between">
                    @for($i = 1; $i <= $totalSteps; $i++)
                        <div class="flex items-center {{ $i < $totalSteps ? 'flex-1' : '' }}">
                            <!-- Step Circle -->
                            <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 
                                {{ $step >= $i ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 bg-white text-gray-400' }}
                                {{ $step > $i ? 'bg-green-600 border-green-600' : '' }}
                                cursor-pointer transition-all duration-200 hover:scale-105"
                                wire:click="goToStep({{ $i }})">
                                @if($step > $i)
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <span class="text-sm font-semibold">{{ $i }}</span>
                                @endif
                            </div>
                            
                            <!-- Step Label -->
                            <div class="ml-3">
                                <p class="text-sm font-medium {{ $step >= $i ? 'text-indigo-600' : 'text-gray-500' }}">
                                    @switch($i)
                                        @case(1) Basic Information @break
                                        @case(2) API Configuration @break
                                        @case(3) Authentication & Fields @break
                                        @case(4) AI & Success Configuration @break
                                    @endswitch
                                </p>
                            </div>

                            <!-- Progress Line -->
                            @if($i < $totalSteps)
                                <div class="flex-1 mx-4">
                                    <div class="h-0.5 {{ $step > $i ? 'bg-green-600' : ($step >= $i ? 'bg-indigo-600' : 'bg-gray-300') }} transition-all duration-300"></div>
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="bg-white rounded-lg border shadow-sm">
            <!-- Step 1: Basic Information -->
            @if($step === 1)
                <div class="p-8 space-y-8">
                    <div class="text-center mb-8">
                        <div class="mx-auto w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Let's start with the basics</h2>
                        <p class="text-gray-600 mt-2">Tell us about your API integration and what it will do.</p>
                    </div>

                    <div class="max-w-2xl mx-auto space-y-6">
                        <flux:field>
                            <flux:label>Integration Name <span class="text-red-500">*</span></flux:label>
                            <flux:input 
                                wire:model="integrationForm.name" 
                                placeholder="Order Status API" 
                                class="text-lg" />
                            <flux:description>Give your integration a descriptive name</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>Description</flux:label>
                            <flux:textarea 
                                wire:model="integrationForm.description" 
                                placeholder="Check order status and delivery information for customers" 
                                rows="3"
                                class="text-base" />
                            <flux:description>Describe what this integration does (optional but recommended)</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>What does this API do? <span class="text-red-500">*</span></flux:label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <input type="radio" id="get_data" wire:model="integrationForm.action_type" value="get_data" class="sr-only peer">
                                    <label for="get_data" class="flex flex-col p-6 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Get Data</h3>
                                        <p class="text-gray-600 text-sm">Retrieve information from external systems like order status, user profiles, or tracking data.</p>
                                    </label>
                                </div>
                                
                                <div class="relative">
                                    <input type="radio" id="submit_data" wire:model="integrationForm.action_type" value="submit_data" class="sr-only peer">
                                    <label for="submit_data" class="flex flex-col p-6 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all duration-200">
                                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8l-4 4m0 0l4 4m-4-4h18"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Submit Data</h3>
                                        <p class="text-gray-600 text-sm">Send information to external systems like creating tickets, submitting forms, or updating records.</p>
                                    </label>
                                </div>
                            </div>
                        </flux:field>
                    </div>
                </div>
            @endif

            <!-- Step 2: API Configuration -->
            @if($step === 2)
                <div class="p-8 space-y-8">
                    <div class="text-center mb-8">
                        <div class="mx-auto w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Configure your API endpoint</h2>
                        <p class="text-gray-600 mt-2">Set up the technical details of your API connection.</p>
                    </div>

                    <div class="max-w-2xl mx-auto space-y-6">
                        <flux:field>
                            <flux:label>API URL <span class="text-red-500">*</span></flux:label>
                            <flux:input 
                                type="url" 
                                wire:model="integrationForm.api_url" 
                                placeholder="https://api.example.com/orders/{order_id}"
                                class="font-mono" />
                            <flux:description>
                                The complete URL of your API endpoint. 
                                <strong>Dynamic routes supported:</strong> Use {parameter} for path parameters.<br>
                                <strong>Examples:</strong><br>
                                • Static: <code>https://api.example.com/orders</code><br>
                                • Dynamic: <code>https://api.example.com/orders/{order_id}</code><br>
                                • Multiple params: <code>https://api.example.com/users/{user_id}/orders/{order_id}</code>
                            </flux:description>
                        </flux:field>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label>HTTP Method <span class="text-red-500">*</span></flux:label>
                                <flux:select wire:model="integrationForm.http_method">
                                    @foreach(\App\Models\CustomApiIntegration::getHttpMethodOptions() as $method => $label)
                                        <flux:select.option value="{{ $method }}">{{ $label }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:description>The HTTP method your API expects</flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>Timeout (seconds) <span class="text-red-500">*</span></flux:label>
                                <flux:input 
                                    type="number" 
                                    wire:model="integrationForm.timeout" 
                                    min="1" 
                                    max="15" 
                                    value="10" />
                                <flux:description>
                                    <strong>Recommended: 5-10 seconds.</strong> Timeout is capped at 15 seconds to prevent execution timeouts. 
                                    If your API typically takes longer, consider optimizing the endpoint or contact support.
                                </flux:description>
                            </flux:field>
                        </div>

                        <!-- Preview -->
                        @if($integrationForm['api_url'])
                            <div class="bg-gray-50 rounded-lg p-4 border">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">API Preview</h4>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 bg-{{ $integrationForm['http_method'] === 'GET' ? 'blue' : ($integrationForm['http_method'] === 'POST' ? 'green' : 'yellow') }}-100 text-{{ $integrationForm['http_method'] === 'GET' ? 'blue' : ($integrationForm['http_method'] === 'POST' ? 'green' : 'yellow') }}-800 rounded text-xs font-bold">
                                        {{ $integrationForm['http_method'] }}
                                    </span>
                                    <span class="font-mono text-sm text-gray-900">{{ $integrationForm['api_url'] }}</span>
                                </div>
                            </div>
                        @endif

                        <!-- Performance Tips -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-blue-800">API Performance Tips</h4>
                                    <div class="mt-1 text-sm text-blue-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li><strong>Timeout Limit:</strong> Maximum 15 seconds to prevent execution timeouts</li>
                                            <li><strong>Recommended:</strong> 5-10 seconds for most APIs</li>
                                            <li><strong>Testing:</strong> Test your API manually first to ensure it responds quickly</li>
                                            <li><strong>Dynamic Routes:</strong> Use {parameter} syntax for path parameters - they'll be replaced with actual values</li>
                                            <li><strong>Optimization:</strong> If your API is slow, consider adding caching or database indexes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Step 3: Authentication & Input Fields -->
            @if($step === 3)
                <div class="p-8 space-y-8">
                    <div class="text-center mb-8">
                        <div class="mx-auto w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Authentication & Input Fields</h2>
                        <p class="text-gray-600 mt-2">Set up security and define what data your API needs.</p>
                    </div>

                    <div class="max-w-2xl mx-auto space-y-8">
                        <!-- Authentication -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900">Authentication</h3>
                            
                            <flux:field>
                                <flux:label>Authentication Type</flux:label>
                                <flux:select wire:model.live="integrationForm.auth_type">
                                    @foreach(\App\Models\CustomApiIntegration::getAuthTypeOptions() as $type => $label)
                                        <flux:select.option value="{{ $type }}">{{ $label }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </flux:field>

                            @if($integrationForm['auth_type'] === 'bearer')
                                <flux:field>
                                    <flux:label>Bearer Token <span class="text-red-500">*</span></flux:label>
                                    <flux:input type="password" wire:model="integrationForm.auth_config.token" placeholder="Your Bearer token" class="font-mono" />
                                    <flux:description>The Bearer token for authentication</flux:description>
                                </flux:field>
                            @elseif($integrationForm['auth_type'] === 'api_key')
                                <div class="grid grid-cols-3 gap-4">
                                    <flux:field>
                                        <flux:label>Key Name <span class="text-red-500">*</span></flux:label>
                                        <flux:input wire:model="integrationForm.auth_config.key" placeholder="X-API-Key" class="font-mono" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Key Value <span class="text-red-500">*</span></flux:label>
                                        <flux:input type="password" wire:model="integrationForm.auth_config.value" placeholder="Your API key" class="font-mono" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Location <span class="text-red-500">*</span></flux:label>
                                        <flux:select wire:model="integrationForm.auth_config.location">
                                            <flux:select.option value="header">Header</flux:select.option>
                                            <flux:select.option value="query">Query Parameter</flux:select.option>
                                        </flux:select>
                                    </flux:field>
                                </div>
                            @elseif($integrationForm['auth_type'] === 'basic')
                                <div class="grid grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label>Username <span class="text-red-500">*</span></flux:label>
                                        <flux:input wire:model="integrationForm.auth_config.username" placeholder="Username" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Password <span class="text-red-500">*</span></flux:label>
                                        <flux:input type="password" wire:model="integrationForm.auth_config.password" placeholder="Password" />
                                    </flux:field>
                                </div>
                            @endif
                        </div>

                        <!-- Input Fields -->
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Input Fields</h3>
                                <flux:button type="button" size="sm" variant="outline" icon="plus" wire:click="addInputField">
                                    Add Field
                                </flux:button>
                            </div>

                            @if(empty($integrationForm['input_schema']))
                                <div class="text-center p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h4 class="mt-2 text-sm font-medium text-gray-900">No input fields configured</h4>
                                    <p class="mt-1 text-sm text-gray-500">Add fields that the AI should collect from users before making the API call.</p>
                                    <div class="mt-6">
                                        <flux:button type="button" variant="primary" icon="plus" wire:click="addInputField">
                                            Add Your First Field
                                        </flux:button>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach($integrationForm['input_schema'] as $fieldName => $field)
                                        <div class="p-6 bg-gray-50 rounded-lg border">
                                            <div class="grid grid-cols-12 gap-4 items-end">
                                                <div class="col-span-2">
                                                    <flux:label>Field Name</flux:label>
                                                    <flux:input 
                                                        value="{{ $fieldName }}"
                                                        wire:blur="updateFieldName('{{ $fieldName }}', $event.target.value)"
                                                        size="sm"
                                                        class="font-mono"
                                                    />
                                                </div>
                                                
                                                <div class="col-span-2">
                                                    <flux:label>Type</flux:label>
                                                    <flux:select 
                                                        wire:model="integrationForm.input_schema.{{ $fieldName }}.type"
                                                        size="sm"
                                                    >
                                                        @foreach(\App\Models\CustomApiIntegration::getFieldTypeOptions() as $type => $label)
                                                            <flux:select.option value="{{ $type }}">{{ $label }}</flux:select.option>
                                                        @endforeach
                                                    </flux:select>
                                                </div>
                                                
                                                <div class="col-span-3">
                                                    <flux:label>Label</flux:label>
                                                    <flux:input 
                                                        wire:model="integrationForm.input_schema.{{ $fieldName }}.label"
                                                        size="sm"
                                                        placeholder="Field Label"
                                                    />
                                                </div>
                                                
                                                <div class="col-span-3">
                                                    <flux:label>Placeholder</flux:label>
                                                    <flux:input 
                                                        wire:model="integrationForm.input_schema.{{ $fieldName }}.placeholder"
                                                        size="sm"
                                                        placeholder="Enter placeholder..."
                                                    />
                                                </div>
                                                
                                                <div class="col-span-1">
                                                    <flux:checkbox 
                                                        label="Required"
                                                        wire:model="integrationForm.input_schema.{{ $fieldName }}.required"
                                                    />
                                                </div>
                                                
                                                <div class="col-span-1">
                                                    <flux:button 
                                                        type="button" 
                                                        size="sm" 
                                                        variant="danger" 
                                                        icon="trash"
                                                        wire:click="removeInputField('{{ $fieldName }}')"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Step 4: AI & Success Configuration -->
            @if($step === 4)
                <div class="p-8 space-y-8">
                    <div class="text-center mb-8">
                        <div class="mx-auto w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">AI & Success Configuration</h2>
                        <p class="text-gray-600 mt-2">Configure how the AI will interact with users and handle responses.</p>
                    </div>

                    <div class="max-w-2xl mx-auto space-y-8">
                        <!-- AI Configuration -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900">AI Configuration</h3>
                            
                            <flux:field>
                                <flux:label>Trigger Keywords</flux:label>
                                <flux:input wire:model="integrationForm.trigger_keywords" placeholder="order status, check order, track package" />
                                <flux:description>Comma-separated keywords that will trigger this API integration</flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>Confirmation Message</flux:label>
                                <flux:textarea wire:model="integrationForm.confirmation_message" rows="3" placeholder="I can help you check your order status. Let me get the required information..." />
                                <flux:description>Message shown when this API integration is triggered</flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>Pre-submission Message</flux:label>
                                <flux:textarea wire:model="integrationForm.ai_rules.pre_submission_message" rows="2" placeholder="I'm now checking your order status..." />
                                <flux:description>Message shown before making the API call</flux:description>
                            </flux:field>
                        </div>

                        <!-- Success Response Configuration -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900">Success Response</h3>
                            
                            <flux:field>
                                <flux:label>Success Title</flux:label>
                                <flux:input wire:model="integrationForm.success_response.title" placeholder="Information Retrieved Successfully" />
                                <flux:description>Title shown when the API call succeeds</flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>Success Message</flux:label>    
                                <flux:textarea wire:model="integrationForm.success_response.message" rows="3" placeholder="I've retrieved the information from our system..." />
                                <flux:description>Message shown when the API call succeeds</flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:checkbox wire:model="integrationForm.success_response.show_response_data">
                                    Show API response data to user
                                </flux:checkbox>
                                <flux:description>Whether to display the API response data in the success message</flux:description>
                            </flux:field>
                        </div>

                        <!-- Final Settings -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900">Final Settings</h3>
                            
                            <flux:field>
                                <flux:checkbox wire:model="integrationForm.is_active">
                                    Enable this integration immediately
                                </flux:checkbox>
                                <flux:description>You can always enable/disable this integration later</flux:description>
                            </flux:field>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation -->
            <div class="px-8 py-6 bg-gray-50 border-t rounded-b-lg">
                <div class="flex items-center justify-between">
                    <div>
                        @if($step > 1)
                            <flux:button variant="outline" wire:click="previousStep" icon="arrow-left">
                                Previous
                            </flux:button>
                        @endif
                    </div>

                    <div class="text-sm text-gray-500">
                        Step {{ $step }} of {{ $totalSteps }}
                    </div>

                    <div class="flex items-center space-x-3">
                        @if($step < $totalSteps)
                            <flux:button variant="primary" wire:click="nextStep" icon-trailing="arrow-right">
                                Next Step
                            </flux:button>
                        @else
                            <flux:button variant="primary" wire:click="saveIntegration" icon="check">
                                {{ $integration ? 'Update' : 'Create' }} Integration
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
