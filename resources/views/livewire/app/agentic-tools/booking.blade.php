<div class="container mx-auto space-y-6">
    <flux:card>
        <flux:tab.group>
            <flux:tabs variant="segmented">
                <flux:tab icon="calendar" name="integrations">Booking Integrations</flux:tab>
                <flux:tab icon="clock" name="requests">Booking Requests</flux:tab>
            </flux:tabs>

            <flux:tab.panel class="space-y-6" name="integrations">
                <div class="space-y-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Booking Integrations</h2>
                            <p class="mt-1 text-sm text-gray-500">Connect your AI assistant with booking platforms like Calendly, Cal.com, Google Calendar, and more.</p>
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
                                <flux:card class="relative">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start space-x-4">
                                            <!-- Platform Icon -->
                                            <div class="flex-shrink-0">
                                                @php
                                                    $platforms = \App\Models\BookingIntegration::getAvailablePlatforms();
                                                    $platformConfig = $platforms[$integration->platform] ?? [];
                                                    $icon = $platformConfig['icon'] ?? 'calendar';
                                                @endphp
                                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <flux:icon name="{{ $icon }}" class="w-6 h-6 text-blue-600" />
                                                </div>
                                            </div>

                                            <!-- Integration Details -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-2">
                                                    <h3 class="text-lg font-medium text-gray-900">{{ $integration->name }}</h3>
                                                    @if($integration->is_default)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Default
                                                        </span>
                                                    @endif
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                        {{ $integration->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                        {{ $integration->status_label }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-500 mt-1">{{ $integration->platform_label }}</p>
                                                
                                                @if($integration->trigger_keywords)
                                                    <div class="mt-2">
                                                        <p class="text-xs text-gray-500">Trigger Keywords:</p>
                                                        <p class="text-sm text-gray-700">{{ $integration->trigger_keywords_string }}</p>
                                                    </div>
                                                @endif

                                                @if($integration->confirmation_message)
                                                    <div class="mt-2">
                                                        <p class="text-xs text-gray-500">Confirmation Message:</p>
                                                        <p class="text-sm text-gray-700">{{ Str::limit($integration->confirmation_message, 100) }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center space-x-2">
                                            @if(!$integration->is_default)
                                                <flux:button 
                                                    size="sm" 
                                                    variant="ghost"
                                                    wire:click="setAsDefault({{ $integration->id }})"
                                                    wire:confirm="Set this as the default booking integration?">
                                                    Set Default
                                                </flux:button>
                                            @endif
                                            
                                            <flux:button 
                                                size="sm" 
                                                variant="ghost"
                                                wire:click="toggleIntegrationStatus({{ $integration->id }})">
                                                {{ $integration->status === 'active' ? 'Disable' : 'Enable' }}
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

                                    <!-- Configuration Preview -->
                                    @if($integration->configuration)
                                        <div class="mt-4 pt-4 border-t border-gray-200">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Configuration</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                @foreach($integration->configuration as $key => $value)
                                                    @if(!empty($value))
                                                        <div>
                                                            <p class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                                                            <p class="text-sm text-gray-900">
                                                                @if(str_contains($key, 'url'))
                                                                    <a href="{{ $value }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                                        {{ Str::limit($value, 50) }}
                                                                    </a>
                                                                @elseif(str_contains($key, 'key') || str_contains($key, 'secret'))
                                                                    {{ str_repeat('*', min(strlen($value), 20)) }}
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </flux:card>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <flux:card>
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 11-8 0v-4m4-4h8m-4-4v8m-4 4h8"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No booking integrations</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by adding your first booking platform integration.</p>
                                <div class="mt-6">
                                    <flux:button icon="plus" variant="primary" wire:click="createIntegration">
                                        Add Integration
                                    </flux:button>
                                </div>
                            </div>
                        </flux:card>
                    @endif

                    <!-- Help Section -->
                    <flux:card>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium">Supported Platforms</h3>
                                <p class="mt-1 text-sm text-gray-500">Connect with popular booking platforms or add your custom solution.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                @foreach(\App\Models\BookingIntegration::getAvailablePlatforms() as $platform => $config)
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <div class="flex items-center space-x-3">
                                            <flux:icon name="{{ $config['icon'] }}" class="w-6 h-6 text-gray-600" />
                                            <div>
                                                <h4 class="font-medium text-gray-900">{{ $config['label'] }}</h4>
                                                <p class="text-xs text-gray-500">{{ $config['description'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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
                            <h3 class="text-lg font-medium text-gray-900">Booking Requests</h3>
                            <p class="mt-1 text-sm text-gray-500">Track and manage booking requests made through your AI assistant.</p>
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
                                <flux:select.option value="completed">Completed</flux:select.option>
                                <flux:select.option value="cancelled">Cancelled</flux:select.option>
                                <flux:select.option value="failed">Failed</flux:select.option>
                            </flux:select>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @php
                            $allRequests = \App\Models\BookingRequest::forWorkspace($this->workspace->id)->get();
                            $stats = [
                                'total' => $allRequests->count(),
                                'pending' => $allRequests->where('status', 'pending')->count(),
                                'completed' => $allRequests->where('status', 'completed')->count(),
                                'cancelled' => $allRequests->where('status', 'cancelled')->count(),
                            ];
                        @endphp

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 11-8 0v-4m4-4h8m-4-4v8m-4 4h8"></path>
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
                                    <p class="text-sm font-medium text-green-900">Completed</p>
                                    <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
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
                                    <p class="text-sm font-medium text-red-900">Cancelled</p>
                                    <p class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requests Table -->
                    @if($this->bookingRequests->count() > 0)
                        <flux:card>
                            <div class="overflow-hidden">
                                <flux:table>
                                    <flux:table.columns>
                                        <flux:table.column>Request ID</flux:table.column>
                                        <flux:table.column>Integration</flux:table.column>
                                        <flux:table.column>Customer Info</flux:table.column>
                                        <flux:table.column>Status</flux:table.column>
                                        <flux:table.column>Date</flux:table.column>
                                        <flux:table.column>Actions</flux:table.column>
                                    </flux:table.columns>

                                    <flux:table.rows>
                                        @foreach($this->bookingRequests as $request)
                                            <flux:table.row>
                                                <flux:table.cell>
                                                    <div class="font-mono text-sm">#{{ $request->id }}</div>
                                                </flux:table.cell>
                                                
                                                <flux:table.cell>
                                                    <div class="text-sm font-medium text-gray-900">{{ $request->bookingIntegration->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $request->bookingIntegration->platform_label }}</div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    <div class="space-y-1">
                                                        @if($request->request_data)
                                                            @foreach($request->request_data as $key => $value)
                                                                @if(!empty($value) && in_array($key, ['name', 'email', 'phone']))
                                                                    <div class="text-xs text-gray-600">
                                                                        <span class="font-medium">{{ ucwords($key) }}:</span>
                                                                        {{ $value }}
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <span class="text-xs text-gray-500">No customer info collected</span>
                                                        @endif
                                                    </div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'completed' => 'bg-green-100 text-green-800',
                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                            'failed' => 'bg-red-100 text-red-800',
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
                                {{ $this->bookingRequests->links() }}
                            </div>
                        </flux:card>
                    @else
                        <flux:card>
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 11-8 0v-4m4-4h8m-4-4v8m-4 4h8"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No booking requests found</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($statusFilter === 'all' && $integrationFilter === 'all')
                                        No booking requests have been made yet.
                                    @else
                                        No booking requests found matching the selected filters.
                                    @endif
                                </p>
                            </div>
                        </flux:card>
                    @endif
                </div>
            </flux:tab.panel>
        </flux:tab.group>
    </flux:card>

    <!-- Create/Edit Integration Modal -->
    @if($showCreateModal || $showIntegrationModal)
        <flux:modal name="integration-modal" wire:model="showCreateModal" wire:model.live="showIntegrationModal">
            <form wire:submit="saveIntegration" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $selectedIntegration ? 'Edit' : 'Create' }} Booking Integration</flux:heading>
                    <flux:subheading>{{ $selectedIntegration ? 'Update' : 'Add' }} your booking platform integration</flux:subheading>
                </div>

                <!-- Platform Selection -->
                @if(!$selectedIntegration)
                    <flux:field>
                        <flux:label>Platform</flux:label>
                        <flux:select wire:model.live="selectedPlatform" placeholder="Choose a platform">
                            @foreach(\App\Models\BookingIntegration::getAvailablePlatforms() as $platform => $config)
                                <flux:select.option value="{{ $platform }}">{{ $config['label'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                @endif

                @if($selectedPlatform)
                    @php
                        $platforms = \App\Models\BookingIntegration::getAvailablePlatforms();
                        $platformConfig = $platforms[$selectedPlatform] ?? [];
                    @endphp

                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Integration Name</flux:label>
                            <flux:input wire:model="integrationForm.name" placeholder="My Calendly Integration" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Status</flux:label>
                            <flux:select wire:model="integrationForm.status">
                                @foreach(\App\Models\BookingIntegration::getStatusOptions() as $status => $label)
                                    <flux:select.option value="{{ $status }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>

                    <!-- Platform Configuration -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-900">{{ $platformConfig['label'] ?? 'Platform' }} Configuration</h4>
                        
                        @foreach($platformConfig['fields'] ?? [] as $fieldKey => $fieldConfig)
                            <flux:field>
                                <flux:label>
                                    {{ $fieldConfig['label'] }}
                                    @if($fieldConfig['required'])
                                        <span class="text-red-500">*</span>
                                    @endif
                                </flux:label>
                                
                                @if($fieldConfig['type'] === 'password')
                                    <flux:input 
                                        type="password"
                                        wire:model="integrationForm.configuration.{{ $fieldKey }}"
                                        placeholder="{{ $fieldConfig['placeholder'] ?? '' }}" />
                                @elseif($fieldConfig['type'] === 'url')
                                    <flux:input 
                                        type="url"
                                        wire:model="integrationForm.configuration.{{ $fieldKey }}"
                                        placeholder="{{ $fieldConfig['placeholder'] ?? '' }}" />
                                @else
                                    <flux:input 
                                        wire:model="integrationForm.configuration.{{ $fieldKey }}"
                                        placeholder="{{ $fieldConfig['placeholder'] ?? '' }}" />
                                @endif
                                
                                @if($fieldConfig['description'])
                                    <flux:description>{{ $fieldConfig['description'] }}</flux:description>
                                @endif
                            </flux:field>
                        @endforeach
                    </div>

                    <!-- AI Configuration -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-900">AI Configuration</h4>
                        
                        <flux:field>
                            <flux:label>Trigger Keywords</flux:label>
                            <flux:input wire:model="integrationForm.trigger_keywords" placeholder="book appointment, schedule meeting, book a call" />
                            <flux:description>Comma-separated keywords that will trigger this booking integration</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>Confirmation Message</flux:label>
                            <flux:textarea wire:model="integrationForm.confirmation_message" rows="3" placeholder="Message shown when booking is triggered..." />
                            <flux:description>Message the AI will show when a booking request is detected</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>AI Instructions</flux:label>
                            <flux:textarea wire:model="integrationForm.ai_instructions" rows="3" placeholder="Instructions for the AI..." />
                            <flux:description>Instructions for how the AI should handle booking requests for this integration</flux:description>
                        </flux:field>
                    </div>

                    <!-- Advanced Settings -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-900">Advanced Settings</h4>
                        
                        <flux:field>
                            <flux:checkbox wire:model="integrationForm.is_default">
                                Set as default integration
                            </flux:checkbox>
                            <flux:description>The default integration will be used when no specific integration is matched</flux:description>
                        </flux:field>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <flux:button variant="ghost" wire:click="closeModals">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" :disabled="!$selectedPlatform">
                        {{ $selectedIntegration ? 'Update' : 'Create' }} Integration
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endif

    <!-- Request Details Modal -->
    @if($showRequestModal && $selectedRequest)
        <flux:modal name="request-details" wire:model="showRequestModal">
            <form wire:submit="updateRequestStatus" class="space-y-6">
                <div>
                    <flux:heading size="lg">Booking Request #{{ $selectedRequest->id }}</flux:heading>
                    <flux:subheading>{{ $selectedRequest->bookingIntegration->name }} - {{ $selectedRequest->created_at->format('M j, Y \a\t g:i A') }}</flux:subheading>
                </div>

                <!-- Request Information -->
                @if($selectedRequest->request_data)
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-900">Customer Information</h4>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            @foreach($selectedRequest->request_data as $key => $value)
                                @if(!empty($value))
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                        <span class="text-sm text-gray-900">{{ $value }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Booking Details -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-900">Booking Details</h4>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Integration:</span>
                            <span class="text-sm text-gray-900">{{ $selectedRequest->bookingIntegration->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Platform:</span>
                            <span class="text-sm text-gray-900">{{ $selectedRequest->bookingIntegration->platform_label }}</span>
                        </div>
                        @if($selectedRequest->booking_url)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Booking URL:</span>
                                <a href="{{ $selectedRequest->booking_url }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800">
                                    View Booking
                                </a>
                            </div>
                        @endif
                        @if($selectedRequest->external_booking_id)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">External ID:</span>
                                <span class="text-sm text-gray-900">{{ $selectedRequest->external_booking_id }}</span>
                            </div>
                        @endif
                        @if($selectedRequest->booking_date)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Scheduled Date:</span>
                                <span class="text-sm text-gray-900">{{ $selectedRequest->booking_date->format('M j, Y g:i A') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Status Management -->
                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="requestStatus">
                        @foreach(\App\Models\BookingRequest::getStatusOptions() as $status => $label)
                            <flux:select.option value="{{ $status }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <!-- Notes -->
                <flux:field>
                    <flux:label>Internal Notes</flux:label>
                    <flux:textarea 
                        wire:model="requestNotes" 
                        placeholder="Add notes about this booking request..."
                        rows="4"
                    />
                </flux:field>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <flux:button variant="ghost" wire:click="closeModals">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Update Request</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div> 