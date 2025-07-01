<div class="container mx-auto space-y-6">
    <flux:card>
        <flux:tab.group>
            <flux:tabs variant="segmented">
                <flux:tab icon="cog-6-tooth" name="configuration">Configuration</flux:tab>
                <flux:tab icon="currency-dollar" name="manage_requests">Manage Requests</flux:tab>
            </flux:tabs>

            <flux:tab.panel class="space-y-6" name="configuration">
                <div class="space-y-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Refund Request Configuration</h2>
                            <p class="mt-1 text-sm text-gray-500">Configure how your AI assistant handles refund requests from customers.</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <flux:button 
                                type="button" 
                                variant="ghost" 
                                icon="arrow-path"
                                wire:click="resetToDefaults"
                                wire:confirm="Are you sure you want to reset to default configuration? This will overwrite all your custom settings.">
                                Reset to Defaults
                            </flux:button>
                            <flux:switch 
                                wire:model.live="agentic_refund_request.enabled"
                                wire:change="toggleTool"
                            />
                        </div>
                    </div>

                    @if($agentic_refund_request['enabled'])
                        <form class="space-y-8" wire:submit.prevent="save">
                            
                            <!-- Schema Configuration -->
                            <flux:card>
                                <div class="space-y-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-medium">Required Information Schema</h3>
                                            <p class="mt-1 text-sm text-gray-500">Configure what information the AI will collect from customers before processing refund requests.</p>
                                        </div>
                                        <flux:button 
                                            type="button" 
                                            size="sm" 
                                            variant="ghost" 
                                            icon="plus"
                                            wire:click="addField">
                                            Add Custom Field
                                        </flux:button>
                                    </div>

                                    <div class="space-y-4">
                                        @if(empty($agentic_refund_request['schema']))
                                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h3 class="text-sm font-medium text-yellow-800">No Fields Configured</h3>
                                                        <p class="mt-1 text-sm text-yellow-700">You need to add at least one field for the refund request tool to work. Click "Add Custom Field" to get started.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @foreach($agentic_refund_request['schema'] as $fieldName => $field)
                                            <div class="grid grid-cols-12 gap-4 items-end p-4 bg-gray-50 rounded-lg">
                                                <div class="col-span-2">
                                                    <flux:input 
                                                        label="Field Name"
                                                        value="{{ $fieldName }}"
                                                        size="sm"
                                                        readonly
                                                    />
                                                </div>
                                                
                                                <div class="col-span-2">
                                                    <flux:select 
                                                        label="Type"
                                                        wire:model="agentic_refund_request.schema.{{ $fieldName }}.type"
                                                        size="sm"
                                                    >
                                                        <flux:select.option value="text">Text</flux:select.option>
                                                        <flux:select.option value="email">Email</flux:select.option>
                                                        <flux:select.option value="textarea">Textarea</flux:select.option>
                                                        <flux:select.option value="number">Number</flux:select.option>
                                                    </flux:select>
                                                </div>
                                                
                                                <div class="col-span-2">
                                                    <flux:input 
                                                        label="Label"
                                                        wire:model="agentic_refund_request.schema.{{ $fieldName }}.label"
                                                        size="sm"
                                                    />
                                                </div>
                                                
                                                <div class="col-span-2">
                                                    <flux:input 
                                                        label="Placeholder"
                                                        wire:model="agentic_refund_request.schema.{{ $fieldName }}.placeholder"
                                                        size="sm"
                                                    />
                                                </div>
                                                
                                                <div class="col-span-2">
                                                    <flux:input 
                                                        label="Validation"
                                                        wire:model="agentic_refund_request.schema.{{ $fieldName }}.validation"
                                                        size="sm"
                                                        placeholder="e.g., email, regex:/pattern/"
                                                    />
                                                </div>
                                                
                                                <div class="col-span-1">
                                                    <flux:checkbox 
                                                        label="Required"
                                                        wire:model="agentic_refund_request.schema.{{ $fieldName }}.required"
                                                    />
                                                </div>
                                                
                                                <div class="col-span-1">
                                                    <flux:button 
                                                        type="button" 
                                                        size="sm" 
                                                        variant="danger" 
                                                        icon="trash"
                                                        wire:click="removeField('{{ $fieldName }}')"
                                                    />
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </flux:card>

                            <!-- AI Rules Configuration -->
                            <flux:card>
                                <div class="space-y-6">
                                    <div>
                                        <h3 class="text-lg font-medium">AI Behavior Rules</h3>
                                        <p class="mt-1 text-sm text-gray-500">Configure how the AI should behave when handling refund requests. All prompts and messages can be customized in any language.</p>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <flux:textarea 
                                                label="Trigger Phrases"
                                                wire:model="agentic_refund_request.ai_rules.trigger_phrases"
                                                rows="2"
                                                placeholder="refund, return, money back, billing issue..."
                                            />
                                            <p class="text-xs text-gray-500 mt-1">Comma-separated phrases that will trigger the refund request tool</p>
                                        </div>

                                        <div>
                                            <flux:textarea 
                                                label="Validation Instructions"
                                                wire:model="agentic_refund_request.ai_rules.validation_instructions"
                                                rows="3"
                                                placeholder="Instructions for the AI on how to validate user input..."
                                            />
                                            <p class="text-xs text-gray-500 mt-1">Tell the AI how to validate and handle user input before processing</p>
                                        </div>

                                        <div>
                                            <flux:textarea 
                                                label="Pre-submission Message"
                                                wire:model="agentic_refund_request.ai_rules.pre_submission_message"
                                                rows="3"
                                                placeholder="Message the AI should show when starting to collect refund information..."
                                            />
                                            <p class="text-xs text-gray-500 mt-1">What the AI says when it first recognizes a refund request</p>
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-3">Field Collection Prompts</h4>
                                            <div class="space-y-3">
                                                @foreach($agentic_refund_request['schema'] as $fieldName => $field)
                                                    <div>
                                                        <flux:textarea 
                                                            label="{{ $field['label'] }} Prompt"
                                                            wire:model="agentic_refund_request.ai_rules.collection_prompts.{{ $fieldName }}"
                                                            rows="2"
                                                            placeholder="How should the AI ask for {{ strtolower($field['label']) }}?"
                                                        />
                                                    </div>
                                                @endforeach
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">Customize how the AI asks for each piece of information</p>
                                        </div>
                                    </div>
                                </div>
                            </flux:card>

                            <!-- Success Response Configuration -->
                            <flux:card>
                                <div class="space-y-6">
                                    <div>
                                        <h3 class="text-lg font-medium">Success Response Configuration</h3>
                                        <p class="mt-1 text-sm text-gray-500">Customize the message shown to customers after their refund request is submitted. All text can be configured in any language.</p>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <flux:input 
                                                label="Success Title"
                                                wire:model="agentic_refund_request.success_response.title"
                                                placeholder="Refund Request Submitted Successfully"
                                            />
                                        </div>

                                        <div>
                                            <flux:textarea 
                                                label="Success Message"
                                                wire:model="agentic_refund_request.success_response.message"
                                                rows="3"
                                                placeholder="Your refund request has been submitted..."
                                            />
                                        </div>

                                        <div>
                                            <flux:textarea 
                                                label="Additional Information"
                                                wire:model="agentic_refund_request.success_response.additional_info"
                                                rows="2"
                                                placeholder="Additional helpful information for customers..."
                                            />
                                        </div>

                                        <div>
                                            <flux:checkbox 
                                                label="Show Request Details"
                                                wire:model="agentic_refund_request.success_response.show_details"
                                            />
                                            <p class="text-xs text-gray-500 mt-1">Whether to show the submitted information back to the customer</p>
                                        </div>
                                    </div>
                                </div>
                            </flux:card>

                            <!-- Save Button -->
                            <div class="flex justify-end">
                                <flux:button icon="check" type="submit" variant="primary">
                                    Save Configuration
                                </flux:button>
                            </div>
                        </form>

                        <!-- Preview Section -->
                        <flux:card>
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-lg font-medium">Configuration Preview</h3>
                                    <p class="mt-1 text-sm text-gray-500">Preview how your refund request system will work.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Schema Preview -->
                                    <div class="space-y-3">
                                        <h4 class="font-medium text-gray-700">Required Fields</h4>
                                        <div class="space-y-2">
                                            @foreach($agentic_refund_request['schema'] as $fieldName => $field)
                                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                                    <span class="text-sm">{{ $field['label'] }}</span>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">{{ $field['type'] }}</span>
                                                        @if($field['required'])
                                                            <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded">Required</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- AI Rules Preview -->
                                    <div class="space-y-3">
                                        <h4 class="font-medium text-gray-700">AI Behavior</h4>
                                        <div class="space-y-2 text-sm">
                                            <div>
                                                <span class="font-medium">Triggers:</span>
                                                <p class="text-gray-600">{{ $agentic_refund_request['ai_rules']['trigger_phrases'] ?? 'Not configured' }}</p>
                                            </div>
                                            <div>
                                                <span class="font-medium">Pre-message:</span>
                                                <p class="text-gray-600">{{ $agentic_refund_request['ai_rules']['pre_submission_message'] ?? 'Not configured' }}</p>
                                            </div>
                                            <div>
                                                <span class="font-medium">Validation:</span>
                                                <p class="text-gray-600 text-xs">{{ Str::limit($agentic_refund_request['ai_rules']['validation_instructions'] ?? 'Not configured', 100) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Success Response Preview -->
                                <div class="mt-6">
                                    <h4 class="font-medium text-gray-700 mb-3">Success Response Preview</h4>
                                    <div class="p-4 bg-gray-50 rounded-lg border">
                                        <div class="bg-white border border-gray-200 rounded-lg p-4" style="background: #f8f9fa; border: 1px solid #dee2e6;">
                                            <h5 class="text-green-600 font-medium mb-2">✅ {{ $agentic_refund_request['success_response']['title'] ?? 'Refund Request Submitted Successfully' }}</h5>
                                            <p class="text-sm text-gray-700 mb-2">{{ $agentic_refund_request['success_response']['message'] ?? 'Your refund request has been submitted...' }}</p>
                                            
                                            @if($agentic_refund_request['success_response']['show_details'] ?? true)
                                                <p class="text-sm font-medium text-gray-700 mb-1">Request Details:</p>
                                                <ul class="text-sm text-gray-600 mb-2 pl-4">
                                                    @foreach($agentic_refund_request['schema'] as $fieldName => $field)
                                                        <li>• <strong>{{ $field['label'] }}:</strong> [Customer's {{ strtolower($field['label']) }}]</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            
                                            @if(!empty($agentic_refund_request['success_response']['additional_info']))
                                                <p class="text-xs text-gray-500">{{ $agentic_refund_request['success_response']['additional_info'] }}</p>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-2">
                                            <strong>Note:</strong> All text can be customized in any language. Field labels will automatically use the labels you've configured above.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </flux:card>

                    @else
                        <!-- Disabled State -->
                        <flux:card>
                            <div class="text-center py-12">
                                <div class="mx-auto h-12 w-12 text-gray-400">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                </div>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Refund Request Tool Disabled</h3>
                                <p class="mt-1 text-sm text-gray-500">Enable the toggle above to configure refund request handling.</p>
                            </div>
                        </flux:card>
                    @endif

                    <!-- Help Section -->
                    <flux:card>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium">How It Works</h3>
                                <p class="mt-1 text-sm text-gray-500">Understanding the refund request system.</p>
                            </div>

                            <div class="space-y-3 text-sm">
                                <div class="p-4 bg-purple-50 rounded-lg">
                                    <h4 class="font-medium text-purple-900">Automatic Detection</h4>
                                    <p class="text-purple-700 mt-1">The AI automatically detects when customers want to request refunds based on the trigger phrases you configure.</p>
                                </div>
                                
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <h4 class="font-medium text-green-900">Flexible Schema</h4>
                                    <p class="text-green-700 mt-1">You can add, remove, and customize any fields according to your business needs. All fields are optional and can be deleted or modified.</p>
                                </div>
                                
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <h4 class="font-medium text-blue-900">Information Collection</h4>
                                    <p class="text-blue-700 mt-1">The AI will systematically collect all configured information using your custom prompts and validate the data according to your rules.</p>
                                </div>
                                
                                <div class="p-4 bg-yellow-50 rounded-lg">
                                    <h4 class="font-medium text-yellow-900">Request Processing</h4>
                                    <p class="text-yellow-700 mt-1">Once all information is collected and validated, the request is stored in your database and the customer receives your custom success message.</p>
                                </div>

                                <div class="p-4 bg-purple-50 rounded-lg">
                                    <h4 class="font-medium text-purple-900">Field Types & Validation</h4>
                                    <ul class="text-purple-700 mt-1 space-y-1">
                                        <li><strong>Text:</strong> Basic text input with optional regex validation</li>
                                        <li><strong>Email:</strong> Automatically validates email format</li>
                                        <li><strong>Textarea:</strong> Multi-line text input for longer responses</li>
                                        <li><strong>Number:</strong> Numeric input only</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                </div>
            </flux:tab.panel>

            <flux:tab.panel name="manage_requests">
                <div class="space-y-6">
                    <!-- Header with stats and filter -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Refund Requests</h3>
                            <p class="mt-1 text-sm text-gray-500">Manage and track customer refund requests submitted through your AI assistant.</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <flux:select wire:model.live="statusFilter" placeholder="Filter by status">
                                <flux:select.option value="all">All Requests</flux:select.option>
                                <flux:select.option value="pending">Pending</flux:select.option>
                                <flux:select.option value="processing">Processing</flux:select.option>
                                <flux:select.option value="approved">Approved</flux:select.option>
                                <flux:select.option value="denied">Denied</flux:select.option>
                                <flux:select.option value="completed">Completed</flux:select.option>
                            </flux:select>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        @php
                            $allRequests = \App\Models\RefundRequest::whereHas('conversation', function($q) {
                                $q->where('workspace_id', $this->workspace->id);
                            })->get();
                            $stats = [
                                'total' => $allRequests->count(),
                                'pending' => $allRequests->where('status', 'pending')->count(),
                                'processing' => $allRequests->where('status', 'processing')->count(),
                                'approved' => $allRequests->where('status', 'approved')->count(),
                                'completed' => $allRequests->where('status', 'completed')->count(),
                            ];
                        @endphp

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-blue-900">Total</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-yellow-900">Pending</p>
                                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-purple-900">Processing</p>
                                    <p class="text-2xl font-bold text-purple-600">{{ $stats['processing'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-900">Approved</p>
                                    <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Completed</p>
                                    <p class="text-2xl font-bold text-gray-600">{{ $stats['completed'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requests Table -->
                    @if($this->refundRequests->count() > 0)
                        <flux:card>
                            <div class="overflow-hidden">
                                <flux:table>
                                    <flux:table.columns>
                                        <flux:table.column>Request ID</flux:table.column>
                                        <flux:table.column>Customer Info</flux:table.column>
                                        <flux:table.column>Request Data</flux:table.column>
                                        <flux:table.column>Status</flux:table.column>
                                        <flux:table.column>Date</flux:table.column>
                                        <flux:table.column>Actions</flux:table.column>
                                    </flux:table.columns>

                                    <flux:table.rows>
                                        @foreach($this->refundRequests as $request)
                                            <flux:table.row>
                                                <flux:table.cell>
                                                    <div class="font-mono text-sm">#{{ $request->id }}</div>
                                                </flux:table.cell>
                                                
                                                <flux:table.cell>
                                                    <div class="space-y-1">
                                                        @if($request->getRequestField('email'))
                                                            <div class="text-sm font-medium text-gray-900">{{ $request->getRequestField('email') }}</div>
                                                        @endif
                                                        @if($request->getRequestField('sale_id'))
                                                            <div class="text-xs text-gray-500">Sale: {{ $request->getRequestField('sale_id') }}</div>
                                                        @endif
                                                    </div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    <div class="space-y-1">
                                                        @foreach(($request->request_data ?? []) as $key => $value)
                                                            @if(!in_array($key, ['email', 'sale_id']) && !empty($value))
                                                                <div class="text-xs text-gray-600">
                                                                    <span class="font-medium">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                                                    {{ Str::limit($value, 30) }}
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'processing' => 'bg-purple-100 text-purple-800',
                                                            'approved' => 'bg-green-100 text-green-800',
                                                            'denied' => 'bg-red-100 text-red-800',
                                                            'completed' => 'bg-gray-100 text-gray-800',
                                                        ];
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                        {{ $request->status_label }}
                                                    </span>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    <div class="text-sm text-gray-900">{{ $request->created_at->format('M j, Y') }}</div>
                                                    <div class="text-xs text-gray-500">{{ $request->created_at->format('g:i A') }}</div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    <flux:button size="sm" variant="ghost" wire:click="viewRequest({{ $request->id }})">
                                                        View Details
                                                    </flux:button>
                                                </flux:table.cell>
                                            </flux:table.row>
                                        @endforeach
                                    </flux:table.rows>
                                </flux:table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-4">
                                {{ $this->refundRequests->links() }}
                            </div>
                        </flux:card>
                    @else
                        <flux:card>
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No refund requests</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($statusFilter === 'all')
                                        No refund requests have been submitted yet.
                                    @else
                                        No {{ $statusFilter }} refund requests found.
                                    @endif
                                </p>
                            </div>
                        </flux:card>
                    @endif
                </div>
            </flux:tab.panel>
        </flux:tab.group>
    </flux:card>

    <!-- Request Details Modal -->
    @if($showRequestModal && $selectedRequest)
        <flux:modal name="request-details" wire:model="showRequestModal">
            <form wire:submit="updateRequestStatus" class="space-y-6">
                <div>
                    <flux:heading size="lg">Refund Request #{{ $selectedRequest->id }}</flux:heading>
                    <flux:subheading>Submitted {{ $selectedRequest->created_at->format('M j, Y \a\t g:i A') }}</flux:subheading>
                </div>

                <!-- Request Information -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-900">Request Information</h4>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        @foreach(($selectedRequest->request_data ?? []) as $key => $value)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                <span class="text-sm text-gray-900">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Status Management -->
                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="requestStatus">
                        <flux:select.option value="pending">Pending</flux:select.option>
                        <flux:select.option value="processing">Processing</flux:select.option>
                        <flux:select.option value="approved">Approved</flux:select.option>
                        <flux:select.option value="denied">Denied</flux:select.option>
                        <flux:select.option value="completed">Completed</flux:select.option>
                    </flux:select>
                </flux:field>

                <!-- Notes -->
                <flux:field>
                    <flux:label>Internal Notes</flux:label>
                    <flux:textarea 
                        wire:model="requestNotes" 
                        placeholder="Add notes about this refund request..."
                        rows="4"
                    />
                </flux:field>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <flux:button variant="ghost" wire:click="closeModal">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Update Request</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div>
