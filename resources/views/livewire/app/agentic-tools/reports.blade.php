<div class="container mx-auto space-y-6">
    <flux:card>
        <flux:tab.group>
            <flux:tabs variant="segmented">
                <flux:tab icon="cog-6-tooth" name="configuration">Configuration</flux:tab>
                <flux:tab icon="flag" name="manage_reports">Manage Reports</flux:tab>
            </flux:tabs>

            <flux:tab.panel class="space-y-6" name="configuration">
                <div class="space-y-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Report System Configuration</h2>
                            <p class="mt-1 text-sm text-gray-500">Configure how your AI assistant handles different types of reports from customers.</p>
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
                                wire:model.live="agentic_reports.enabled"
                                wire:change="toggleTool"
                            />
                        </div>
                    </div>

                    @if($agentic_reports['enabled'])
                        <form class="space-y-8" wire:submit.prevent="save">
                            
                            <!-- Report Types Configuration -->
                            <flux:card>
                                <div class="space-y-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-medium">Report Types</h3>
                                            <p class="mt-1 text-sm text-gray-500">Configure the different types of reports customers can submit. Each type can have custom trigger keywords and confirmation messages.</p>
                                        </div>
                                        <flux:button 
                                            type="button" 
                                            size="sm" 
                                            variant="ghost" 
                                            icon="plus"
                                            wire:click="addReportType">
                                            Add Report Type
                                        </flux:button>
                                    </div>

                                    <div class="space-y-6">
                                        @if(empty($agentic_reports['types']))
                                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h3 class="text-sm font-medium text-yellow-800">No Report Types Configured</h3>
                                                        <p class="mt-1 text-sm text-yellow-700">You need to add at least one report type for the system to work. Click "Add Report Type" to get started.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @foreach($agentic_reports['types'] as $typeName => $type)
                                            <div class="border border-gray-200 rounded-lg p-6 space-y-4 {{ $type['enabled'] ? 'bg-white' : 'bg-gray-50' }}">
                                                <!-- Type Header -->
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <flux:switch 
                                                            wire:model="agentic_reports.types.{{ $typeName }}.enabled"
                                                        />
                                                        <div>
                                                            <h4 class="text-lg font-medium text-gray-900">{{ $type['label'] }}</h4>
                                                            <p class="text-sm text-gray-500">Type: {{ $typeName }}</p>
                                                        </div>
                                                    </div>
                                                    <flux:button 
                                                        type="button" 
                                                        size="sm" 
                                                        variant="danger" 
                                                        icon="trash"
                                                        wire:click="removeReportType('{{ $typeName }}')"
                                                        wire:confirm="Are you sure you want to delete this report type? This action cannot be undone."
                                                    />
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <!-- Label -->
                                                    <div>
                                                        <flux:input 
                                                            label="Display Label"
                                                            wire:model="agentic_reports.types.{{ $typeName }}.label"
                                                            placeholder="Bug Report, Abuse Report, etc."
                                                        />
                                                    </div>

                                                    <!-- Trigger Keywords -->
                                                    <div>
                                                        <flux:input 
                                                            label="Trigger Keywords"
                                                            wire:model="agentic_reports.types.{{ $typeName }}.trigger_keywords"
                                                            placeholder="bug, error, problem, issue"
                                                        />
                                                        <p class="text-xs text-gray-500 mt-1">Comma-separated keywords that trigger this report type</p>
                                                    </div>
                                                </div>

                                                <!-- Confirmation Message -->
                                                <div>
                                                    <flux:textarea 
                                                        label="Confirmation Message"
                                                        wire:model="agentic_reports.types.{{ $typeName }}.confirmation_message"
                                                        rows="3"
                                                        placeholder="Message shown to user when this report type is triggered..."
                                                    />
                                                    <p class="text-xs text-gray-500 mt-1">This message will be shown when the user triggers the keywords</p>
                                                </div>

                                                <!-- Rules -->
                                                <div>
                                                    <flux:textarea 
                                                        label="AI Processing Rules"
                                                        wire:model="agentic_reports.types.{{ $typeName }}.rules"
                                                        rows="3"
                                                        placeholder="Instructions for how the AI should handle this type of report..."
                                                    />
                                                    <p class="text-xs text-gray-500 mt-1">Guidelines for the AI on how to collect and process this type of report</p>
                                                </div>
                                            </div>
                                        @endforeach
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
                                    <h3 class="text-lg font-medium">Active Report Types Preview</h3>
                                    <p class="mt-1 text-sm text-gray-500">Preview of currently active report types and their trigger keywords.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($agentic_reports['types'] as $typeName => $type)
                                        @if($type['enabled'])
                                            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="font-medium text-green-900">{{ $type['label'] }}</h4>
                                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded">Active</span>
                                                </div>
                                                <div class="space-y-2 text-sm">
                                                    <div>
                                                        <span class="font-medium text-green-700">Triggers:</span>
                                                        <p class="text-green-600 text-xs">{{ $type['trigger_keywords'] }}</p>
                                                    </div>
                                                    <div>
                                                        <span class="font-medium text-green-700">Message:</span>
                                                        <p class="text-green-600 text-xs">{{ Str::limit($type['confirmation_message'], 80) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if(collect($agentic_reports['types'])->where('enabled', true)->isEmpty())
                                        <div class="col-span-full p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-center">
                                            <p class="text-yellow-700">No active report types. Enable at least one report type to start collecting reports.</p>
                                        </div>
                                    @endif
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
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Report System Disabled</h3>
                                <p class="mt-1 text-sm text-gray-500">Enable the toggle above to configure report handling.</p>
                            </div>
                        </flux:card>
                    @endif

                    <!-- Help Section -->
                    <flux:card>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium">How The Report System Works</h3>
                                <p class="mt-1 text-sm text-gray-500">Understanding how customers can submit different types of reports.</p>
                            </div>

                            <div class="space-y-3 text-sm">
                                <div class="p-4 bg-purple-50 rounded-lg">
                                    <h4 class="font-medium text-purple-900">Automatic Detection</h4>
                                    <p class="text-purple-700 mt-1">The AI automatically detects when customers want to submit reports based on the trigger keywords you configure for each report type.</p>
                                </div>
                                
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <h4 class="font-medium text-green-900">Flexible Report Types</h4>
                                    <p class="text-green-700 mt-1">You can create custom report types for your specific needs - bug reports, abuse reports, security issues, or any other type of feedback.</p>
                                </div>
                                
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <h4 class="font-medium text-blue-900">Confirmation Flow</h4>
                                    <p class="text-blue-700 mt-1">When triggered, the AI shows your custom confirmation message and waits for the user to provide detailed information about their report.</p>
                                </div>
                                
                                <div class="p-4 bg-yellow-50 rounded-lg">
                                    <h4 class="font-medium text-yellow-900">Intelligent Processing</h4>
                                    <p class="text-yellow-700 mt-1">Each report type has custom AI rules that guide how the information should be collected and processed according to your specific requirements.</p>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                </div>
            </flux:tab.panel>

            <flux:tab.panel name="manage_reports">
                <div class="space-y-6">
                    <!-- Header with stats and filters -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Customer Reports</h3>
                            <p class="mt-1 text-sm text-gray-500">Manage and track customer reports submitted through your AI assistant.</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <flux:select wire:model.live="typeFilter" placeholder="Filter by type">
                                <flux:select.option value="all">All Types</flux:select.option>
                                @foreach($this->reportTypesForFilter as $type => $label)
                                    <flux:select.option value="{{ $type }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:select wire:model.live="statusFilter" placeholder="Filter by status">
                                <flux:select.option value="all">All Status</flux:select.option>
                                <flux:select.option value="pending">Pending</flux:select.option>
                                <flux:select.option value="investigating">Investigating</flux:select.option>
                                <flux:select.option value="resolved">Resolved</flux:select.option>
                                <flux:select.option value="dismissed">Dismissed</flux:select.option>
                            </flux:select>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @php
                            $allReports = \App\Models\Report::whereHas('conversation', function($q) {
                                $q->where('workspace_id', $this->workspace->id);
                            })->get();
                            $stats = [
                                'total' => $allReports->count(),
                                'pending' => $allReports->where('status', 'pending')->count(),
                                'investigating' => $allReports->where('status', 'investigating')->count(),
                                'resolved' => $allReports->where('status', 'resolved')->count(),
                            ];
                        @endphp

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-blue-900">Total Reports</p>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-purple-900">Investigating</p>
                                    <p class="text-2xl font-bold text-purple-600">{{ $stats['investigating'] }}</p>
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
                                    <p class="text-sm font-medium text-green-900">Resolved</p>
                                    <p class="text-2xl font-bold text-green-600">{{ $stats['resolved'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reports Table -->
                    @if($this->reports->count() > 0)
                        <flux:card>
                            <div class="overflow-hidden">
                                <flux:table>
                                    <flux:table.columns>
                                        <flux:table.column>Report ID</flux:table.column>
                                        <flux:table.column>Type</flux:table.column>
                                        <flux:table.column>Content</flux:table.column>
                                        <flux:table.column>Status</flux:table.column>
                                        <flux:table.column>Date</flux:table.column>
                                        <flux:table.column>Actions</flux:table.column>
                                    </flux:table.columns>

                                    <flux:table.rows>
                                        @foreach($this->reports as $report)
                                            <flux:table.row>
                                                <flux:table.cell>
                                                    <div class="font-mono text-sm">#{{ $report->id }}</div>
                                                </flux:table.cell>
                                                
                                                <flux:table.cell>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $report->report_type_label }}
                                                    </span>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    <div class="text-sm text-gray-900">
                                                        {{ Str::limit($report->report_content, 100) }}
                                                    </div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'investigating' => 'bg-purple-100 text-purple-800',
                                                            'resolved' => 'bg-green-100 text-green-800',
                                                            'dismissed' => 'bg-red-100 text-red-800',
                                                        ];
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$report->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                        {{ $report->status_label }}
                                                    </span>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    <div class="text-sm text-gray-900">{{ $report->created_at->format('M j, Y') }}</div>
                                                    <div class="text-xs text-gray-500">{{ $report->created_at->format('g:i A') }}</div>
                                                </flux:table.cell>

                                                <flux:table.cell>
                                                    <flux:button size="sm" variant="ghost" wire:click="viewReport({{ $report->id }})">
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
                                {{ $this->reports->links() }}
                            </div>
                        </flux:card>
                    @else
                        <flux:card>
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No reports found</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($statusFilter === 'all' && $typeFilter === 'all')
                                        No reports have been submitted yet.
                                    @else
                                        No reports found matching the selected filters.
                                    @endif
                                </p>
                            </div>
                        </flux:card>
                    @endif
                </div>
            </flux:tab.panel>
        </flux:tab.group>
    </flux:card>

    <!-- Report Details Modal -->
    @if($showReportModal && $selectedReport)
        <flux:modal name="report-details" wire:model="showReportModal">
            <form wire:submit="updateReportStatus" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $selectedReport->report_type_label }} #{{ $selectedReport->id }}</flux:heading>
                    <flux:subheading>Submitted {{ $selectedReport->created_at->format('M j, Y \a\t g:i A') }}</flux:subheading>
                </div>

                <!-- Report Content -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-900">Report Content</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $selectedReport->report_content }}</p>
                    </div>
                </div>

                <!-- Status Management -->
                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="reportStatus">
                        <flux:select.option value="pending">Pending</flux:select.option>
                        <flux:select.option value="investigating">Investigating</flux:select.option>
                        <flux:select.option value="resolved">Resolved</flux:select.option>
                        <flux:select.option value="dismissed">Dismissed</flux:select.option>
                    </flux:select>
                </flux:field>

                <!-- Notes -->
                <flux:field>
                    <flux:label>Internal Notes</flux:label>
                    <flux:textarea 
                        wire:model="reportNotes" 
                        placeholder="Add notes about this report..."
                        rows="4"
                    />
                </flux:field>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <flux:button variant="ghost" wire:click="closeModal">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Update Report</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div> 