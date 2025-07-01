<div class="container mx-auto space-y-6">
    <flux:card>
        <flux:tab.group>
            <flux:tabs variant="segmented">
                <flux:tab icon="globe-alt" name="integrations">API Integrations</flux:tab>
                <flux:tab icon="document-text" name="requests">API Requests</flux:tab>
            </flux:tabs>

            <flux:tab.panel class="space-y-6" name="integrations">
                <div class="space-y-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Custom API Integrations</h2>
                            <p class="mt-1 text-sm text-gray-500">Connect your AI assistant with external APIs for order status, ticket creation, and more.</p>
                        </div>
                        <flux:button 
                            icon="plus" 
                            variant="primary"
                            wire:click="createIntegration">
                            Add Integration
                        </flux:button>
                    </div>

                    <!-- Integrations List -->
                    @if($this->integrations->count() > 0)
                        <div class="grid grid-cols-1 gap-6">
                            @foreach($this->integrations as $integration)
                                <flux:card class="relative hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start space-x-4">
                                            <!-- Integration Icon -->
                                            <div class="flex-shrink-0">
                                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Integration Details -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-3 mb-2">
                                                    <h3 class="text-lg font-semibold text-gray-900">{{ $integration->name }}</h3>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                        {{ $integration->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                        {{ $integration->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $integration->action_type_label }}
                                                    </span>
                                                </div>
                                                
                                                @if($integration->description)
                                                    <p class="text-sm text-gray-600 mb-3">{{ $integration->description }}</p>
                                                @endif
                                                
                                                <div class="space-y-2">
                                                    <div class="flex items-center space-x-6 text-xs text-gray-500">
                                                        <span class="flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <strong>Method:</strong> {{ $integration->http_method_label }}
                                                        </span>
                                                        <span class="flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                            </svg>
                                                            <strong>Auth:</strong> {{ $integration->auth_type_label }}
                                                        </span>
                                                        <span class="flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <strong>Timeout:</strong> {{ $integration->timeout }}s
                                                        </span>
                                                    </div>
                                                    
                                                    @if($integration->trigger_keywords)
                                                        <div class="text-xs text-gray-500">
                                                            <span class="flex items-center">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                                </svg>
                                                                <strong>Triggers:</strong> {{ $integration->trigger_keywords_string }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center space-x-2">
                                            <flux:button 
                                                size="sm" 
                                                variant="outline"
                                                icon="beaker"
                                                wire:click="testIntegration({{ $integration->id }})">
                                                Test
                                            </flux:button>
                                            
                                            <flux:button 
                                                size="sm" 
                                                variant="{{ $integration->is_active ? 'outline' : 'primary' }}"
                                                wire:click="toggleIntegrationStatus({{ $integration->id }})">
                                                {{ $integration->is_active ? 'Disable' : 'Enable' }}
                                            </flux:button>
                                            
                                            <flux:button 
                                                size="sm" 
                                                variant="ghost" 
                                                icon="pencil"
                                                wire:click="editIntegration({{ $integration->id }})">
                                                Edit
                                            </flux:button>
                                            
                                            <flux:button 
                                                size="sm" 
                                                variant="danger" 
                                                icon="trash"
                                                wire:click="deleteIntegration({{ $integration->id }})"
                                                wire:confirm="Are you sure you want to delete this integration? This action cannot be undone.">
                                                Delete
                                            </flux:button>
                                        </div>
                                    </div>

                                    <!-- API URL and Schema Preview -->
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                                    </svg>
                                                    API Endpoint
                                                </h4>
                                                <div class="bg-gray-50 rounded-lg p-3 border">
                                                    <p class="text-sm font-mono text-gray-900">
                                                        <span class="px-2 py-1 bg-{{ $integration->http_method === 'GET' ? 'blue' : ($integration->http_method === 'POST' ? 'green' : 'yellow') }}-100 text-{{ $integration->http_method === 'GET' ? 'blue' : ($integration->http_method === 'POST' ? 'green' : 'yellow') }}-800 rounded text-xs font-bold mr-2">
                                                            {{ $integration->http_method }}
                                                        </span>
                                                        {{ $integration->api_url }}
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            @if($integration->input_schema)
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        Input Fields ({{ count($integration->input_schema) }})
                                                    </h4>
                                                    <div class="bg-gray-50 rounded-lg p-3 border">
                                                        <div class="space-y-1">
                                                            @foreach(array_slice($integration->input_schema, 0, 3) as $fieldName => $field)
                                                                <div class="text-xs text-gray-600 flex items-center justify-between">
                                                                    <span class="font-medium">{{ $field['label'] ?? ucwords(str_replace('_', ' ', $fieldName)) }}</span>
                                                                    <div class="flex items-center space-x-1">
                                                                        <span class="px-1.5 py-0.5 bg-gray-200 text-gray-700 rounded text-xs">{{ $field['type'] ?? 'text' }}</span>
                                                                        @if($field['required'] ?? false)
                                                                            <span class="text-red-500 text-xs">*</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                            @if(count($integration->input_schema) > 3)
                                                                <div class="text-xs text-gray-500 text-center pt-1 border-t">
                                                                    +{{ count($integration->input_schema) - 3 }} more fields
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </flux:card>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <flux:card>
                            <div class="text-center py-16">
                                <div class="mx-auto w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mb-6">
                                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">No API integrations yet</h3>
                                <p class="text-gray-500 mb-6 max-w-sm mx-auto">Connect your AI assistant with external APIs to enable powerful automation and data retrieval capabilities.</p>
                                <flux:button icon="plus" variant="primary" wire:click="createIntegration">
                                    Create Your First Integration
                                </flux:button>
                            </div>
                        </flux:card>
                    @endif

                    <!-- Help Section -->
                    <flux:card>
                        <div class="space-y-6">
                            <div class="text-center">
                                <h3 class="text-lg font-semibold text-gray-900">How Custom API Integrations Work</h3>
                                <p class="mt-1 text-gray-500">Connect your AI assistant with any external API for powerful automation.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200">
                                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-blue-900 mb-2">Get Data APIs</h4>
                                    <p class="text-blue-700 text-sm">Retrieve information from external systems like order status, user profiles, or tracking information.</p>
                                </div>
                                
                                <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200">
                                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8l-4 4m0 0l4 4m-4-4h18"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-green-900 mb-2">Submit Data APIs</h4>
                                    <p class="text-green-700 text-sm">Send information to external systems like creating tickets, submitting forms, or updating records.</p>
                                </div>
                                
                                <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200">
                                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-purple-900 mb-2">Secure Authentication</h4>
                                    <p class="text-purple-700 text-sm">Support for Bearer tokens, API keys, Basic auth, and custom headers for secure API access.</p>
                                </div>
                                
                                <div class="text-center p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl border border-yellow-200">
                                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-yellow-900 mb-2">AI-Powered Collection</h4>
                                    <p class="text-yellow-700 text-sm">Configure how the AI collects required information from customers before making API calls.</p>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                </div>
            </flux:tab.panel>

            <flux:tab.panel name="requests">
                <div class="space-y-6">
                    <!-- Header with filters -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">API Requests</h3>
                            <p class="mt-1 text-sm text-gray-500">Track and monitor API requests made through your integrations.</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <flux:select wire:model.live="integrationFilter" placeholder="Filter by integration">
                                <flux:select.option value="all">All Integrations</flux:select.option>
                                @foreach($this->integrationsForFilter as $id => $name)
                                    <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:select wire:model.live="statusFilter" placeholder="Filter by status">
                                <flux:select.option value="all">All Status</flux:select.option>
                                <flux:select.option value="pending">Pending</flux:select.option>
                                <flux:select.option value="success">Success</flux:select.option>
                                <flux:select.option value="failed">Failed</flux:select.option>
                            </flux:select>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @php
                            $allRequests = \App\Models\CustomApiRequest::forWorkspace($this->workspace->id)->get();
                            $stats = [
                                'total' => $allRequests->count(),
                                'pending' => $allRequests->where('status', 'pending')->count(),
                                'success' => $allRequests->where('status', 'success')->count(),
                                'failed' => $allRequests->where('status', 'failed')->count(),
                            ];
                        @endphp

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-blue-900">Total Requests</p>
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

                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-900">Success</p>
                                    <p class="text-2xl font-bold text-green-600">{{ $stats['success'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-900">Failed</p>
                                    <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requests Table -->
                    @if($this->apiRequests->count() > 0)
                        <flux:card>
                            <div class="overflow-hidden">
                                <flux:table>
                                    <flux:table.columns>
                                        <flux:table.column>Request ID</flux:table.column>
                                        <flux:table.column>Integration</flux:table.column>
                                        <flux:table.column>Status</flux:table.column>
                                        <flux:table.column>Response Time</flux:table.column>
                                        <flux:table.column>Date</flux:table.column>
                                        <flux:table.column>Actions</flux:table.column>
                                    </flux:table.columns>

                                    <flux:table.rows>
                                        @foreach($this->apiRequests as $request)
                                            <flux:table.row>
                                                <flux:table.cell>
                                                    <div class="font-mono text-sm">#{{ $request->id }}</div>
                                                </flux:table.cell>
                                                
                                                <flux:table.cell>
                                                    <div class="text-sm font-medium text-gray-900">{{ $request->customApiIntegration->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $request->customApiIntegration->action_type_label }}</div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'success' => 'bg-green-100 text-green-800',
                                                            'failed' => 'bg-red-100 text-red-800',
                                                        ];
                                                    @endphp
                                                    <div class="space-y-1">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                            {{ $request->status_label }}
                                                        </span>
                                                        @if($request->http_status_code)
                                                            <div class="text-xs text-gray-500">HTTP {{ $request->http_status_code }}</div>
                                                        @endif
                                                    </div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    <div class="text-sm text-gray-900">{{ $request->formatted_response_time }}</div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    <div class="text-sm text-gray-900">{{ $request->created_at->format('M j, Y') }}</div>
                                                    <div class="text-xs text-gray-500">{{ $request->created_at->format('g:i A') }}</div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    <div class="flex space-x-2">
                                                        <flux:button size="sm" variant="ghost" wire:click="viewRequest({{ $request->id }})">
                                                            View Details
                                                        </flux:button>
                                                        <a href="{{ route('app.workspace.engagement.conversations', ['uuid' => $this->workspace->uuid, 'conversationUuid' => $request->conversation->uuid]) }}" 
                                                           class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            View Conversation
                                                        </a>
                                                    </div>
                                                </flux:table.cell>
                                            </flux:table.row>
                                        @endforeach
                                    </flux:table.rows>
                                </flux:table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-4">
                                {{ $this->apiRequests->links() }}
                            </div>
                        </flux:card>
                    @else
                        <flux:card>
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No API requests found</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($statusFilter === 'all' && $integrationFilter === 'all')
                                        No API requests have been made yet.
                                    @else
                                        No API requests found matching the selected filters.
                                    @endif
                                </p>
                            </div>
                        </flux:card>
                    @endif
                </div>
            </flux:tab.panel>
        </flux:tab.group>
    </flux:card>

    <!-- Request Details Modal - ONLY modal we keep -->
    @if($showRequestModal && $selectedRequest)
        <flux:modal name="request-details" wire:model="showRequestModal">
            <div class="space-y-6">
                <div>
                    <flux:heading>API Request #{{ $selectedRequest->id }}</flux:heading>
                    <flux:subheading>{{ $selectedRequest->customApiIntegration->name }} - {{ $selectedRequest->created_at->format('M j, Y \a\t g:i A') }}</flux:subheading>
                </div>

                <!-- Request Information -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-900">Request Details</h4>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Integration:</span>
                                <span class="text-gray-900">{{ $selectedRequest->customApiIntegration->name }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Status:</span>
                                <span class="text-gray-900">{{ $selectedRequest->status_label }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Response Time:</span>
                                <span class="text-gray-900">{{ $selectedRequest->formatted_response_time }}</span>
                            </div>
                            @if($selectedRequest->http_status_code)
                                <div>
                                    <span class="font-medium text-gray-700">HTTP Status:</span>
                                    <span class="text-gray-900">{{ $selectedRequest->http_status_code }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Request Data -->
                @if($selectedRequest->request_data)
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-900">Request Data</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <pre class="text-sm text-gray-900 whitespace-pre-wrap">{{ json_encode($selectedRequest->request_data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @endif

                <!-- Response Data -->
                @if($selectedRequest->response_data)
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-900">Response Data</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <pre class="text-sm text-gray-900 whitespace-pre-wrap">{{ json_encode($selectedRequest->response_data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @endif

                <!-- Error Message -->
                @if($selectedRequest->error_message)
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-red-700">Error Message</h4>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-sm text-red-700">{{ $selectedRequest->error_message }}</p>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex justify-end">
                    <flux:button variant="ghost" wire:click="closeModals">Close</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
