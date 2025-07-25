<div>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <flux:icon name="camera" class="text-pink-500 size-8" />
                <flux:heading size="xl">Instagram Integration</flux:heading>
            </div>
            
            @if(!$instagramPage)
                <flux:button wire:click="setupInstagram" variant="primary" icon="plus">
                    Connect Instagram Account
                </flux:button>
            @endif
        </div>

        <flux:callout icon="exclamation-triangle" color="red" class="mb-6">
            <flux:callout.heading>Testing Mode Only</flux:callout.heading>
            <flux:callout.text>
                This Facebook integration module is currently in <span class="font-semibold">testing mode only</span>. We're in the process of requesting official approval as an App from the Meta Platform. During this testing phase:
                <ul class="list-disc ml-5 mt-2 space-y-1">
                    <li>Each customer needs to create their own Meta App</li>
                    <li>The setup process requires multiple manual steps</li>
                    <li>This integration is provided to demonstrate functionality</li>
                </ul>
                Once our official app is approved, the integration process will be significantly simpler (similar to our Discord integration). We appreciate your patience during this testing period.
            </flux:callout.text>
        </flux:callout>
        
        @if($instagramPage)
            <flux:card class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    @if($instagramPage->page_icon)
                        <img src="{{ $instagramPage->page_icon }}" alt="{{ $instagramPage->page_name }}" class="size-12 rounded-full">
                    @else
                        <flux:avatar name="{{ $instagramPage->page_name ?? 'Instagram Account' }}" variant="primary" class="size-12 bg-gradient-to-r from-purple-500 to-pink-500" />
                    @endif
                    <div>
                        <flux:heading size="lg">{{ $instagramPage->page_name ?? 'Setup in progress' }}</flux:heading>
                        @if($instagramPage->page_id)
                            <flux:text size="sm" class="text-gray-500">ID: {{ $instagramPage->page_id }}</flux:text>
                        @elseif(!$instagramPage->is_active)
                            <flux:badge color="amber">Setup incomplete</flux:badge>
                            <a href="{{ route('app.workspace.platforms.instagram.setup', ['uuid' => $workspace->uuid]) }}" class="text-pink-500 hover:underline ml-2">Complete setup</a>
                        @endif
                    </div>
                </div>
                
                @if($instagramPage->is_active)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="p-4 bg-gray-50/50 dark:bg-gray-800/50 rounded-lg">
                            <flux:heading size="sm" class="mb-2">Status</flux:heading>
                            @if($instagramPage->is_active)
                                <flux:badge color="green">Active</flux:badge>
                            @else
                                <flux:badge color="gray">Inactive</flux:badge>
                            @endif
                        </div>
                        
                        <div class="p-4 bg-gray-50/50 dark:bg-gray-800/50 rounded-lg">
                            <flux:heading size="sm" class="mb-2">Handle Messages</flux:heading>
                            @if($instagramPage->handle_messages)
                                <flux:badge color="pink">Enabled</flux:badge>
                            @else
                                <flux:badge color="gray">Disabled</flux:badge>
                            @endif
                        </div>
                        
                        <div class="p-4 bg-gray-50/50 dark:bg-gray-800/50 rounded-lg">
                            <flux:heading size="sm" class="mb-2">Handle Comments</flux:heading>
                            @if($instagramPage->handle_comments)
                                <flux:badge color="pink">Enabled</flux:badge>
                            @else
                                <flux:badge color="gray">Disabled</flux:badge>
                            @endif
                        </div>
                    </div>
                    
                    <flux:card class="p-4 bg-gray-50/50 dark:bg-gray-800/50">
                        <flux:heading size="sm" class="mb-2">Webhook Information</flux:heading>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center">
                                    <flux:text size="sm" class="font-medium">Callback URL:</flux:text>
                                    <button type="button" class="text-pink-500 hover:text-pink-600" 
                                            onclick="navigator.clipboard.writeText('{{ route('api.instagram.webhook', ['page_uuid' => $instagramPage->uuid]) }}')">
                                        <flux:icon name="clipboard" class="size-4" />
                                    </button>
                                </div>
                                <flux:text size="xs" class="font-mono text-gray-500 break-all">{{ route('api.instagram.webhook', ['page_uuid' => $instagramPage->uuid]) }}</flux:text>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center">
                                    <flux:text size="sm" class="font-medium">Verify Token:</flux:text>
                                    <button type="button" class="text-pink-500 hover:text-pink-600" 
                                            onclick="navigator.clipboard.writeText('{{ $instagramPage->page_verify_token }}')">
                                        <flux:icon name="clipboard" class="size-4" />
                                    </button>
                                </div>
                                <flux:text size="xs" class="font-mono text-gray-500 break-all">{{ $instagramPage->page_verify_token }}</flux:text>
                            </div>
                        </div>
                    </flux:card>
                @else
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg mt-4">
                        <div class="flex items-start gap-2">
                            <flux:icon name="exclamation-triangle" class="text-amber-500 mt-0.5" />
                            <div>
                                <flux:text class="font-medium">Setup incomplete</flux:text>
                                <flux:text size="sm" class="text-gray-600">You need to complete the Instagram account setup to use this integration.</flux:text>
                                <div class="mt-2">
                                    <a href="{{ route('app.workspace.platforms.instagram.setup', ['uuid' => $workspace->uuid]) }}" class="inline-flex items-center gap-1 text-pink-500 hover:underline">
                                        <span>Complete setup</span>
                                        <flux:icon name="arrow-right" class="size-4" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </flux:card>
        @else
            <flux:card class="p-6 text-center">
                <flux:icon name="camera" class="size-12 text-gray-400 mx-auto mb-3" />
                <flux:heading size="lg" class="mb-2">No Instagram Account Connected</flux:heading>
                <flux:text class="text-gray-500 mb-6">Connect your Instagram account to start receiving and responding to messages and comments.</flux:text>
                <flux:button wire:click="setupInstagram" variant="primary" icon="plus" class="mx-auto">
                    Connect Instagram Account
                </flux:button>
            </flux:card>
        @endif
    </div>
</div>
