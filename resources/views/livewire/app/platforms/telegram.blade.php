<div>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <flux:icon name="chat-bubble-left-right" class="text-blue-500 size-8" />
                <flux:heading size="xl">Telegram Integration</flux:heading>
            </div>
            
            @if(!$telegramBot)
                <flux:button wire:click="setupTelegram" variant="primary" icon="plus">
                    Connect Telegram Bot
                </flux:button>
            @endif
        </div>
        
        @if($telegramBot)
            <flux:card class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <flux:avatar name="{{ $telegramBot->bot_username ?? 'Telegram Bot' }}" variant="primary" class="size-12" />
                    <div>
                        <flux:heading size="lg">{{ $telegramBot->bot_username ?? 'Setup in progress' }}</flux:heading>
                        @if($telegramBot->bot_token)
                            <flux:text size="sm">@{{ $telegramBot->bot_username }}</flux:text>
                        @elseif(!$telegramBot->is_active)
                            <flux:badge color="amber">Setup incomplete</flux:badge>
                            <a href="{{ route('app.workspace.platforms.telegram.setup', ['uuid' => $workspace->uuid]) }}" class="text-blue-500 hover:underline ml-2">Complete setup</a>
                        @endif
                    </div>
                </div>
                
                @if($telegramBot->is_active)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="p-4 bg-gray-50/50 dark:bg-gray-800/50 rounded-lg">
                            <flux:heading size="sm" class="mb-2">Status</flux:heading>
                            @if($telegramBot->is_active)
                                <flux:badge color="green">Active</flux:badge>
                            @else
                                <flux:badge color="gray">Inactive</flux:badge>
                            @endif
                        </div>
                        
                        <div class="p-4 bg-gray-50/50 dark:bg-gray-800/50 rounded-lg">
                            <flux:heading size="sm" class="mb-2">Command Prefix</flux:heading>
                            <flux:badge color="blue">{{ $telegramBot->command_prefix }}</flux:badge>
                        </div>
                    </div>
                    
                    <flux:card class="p-4 bg-gray-50/50 dark:bg-gray-800/50">
                        <flux:heading size="sm" class="mb-2">Webhook Information</flux:heading>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center">
                                    <flux:text size="sm" class="font-medium">Webhook URL:</flux:text>
                                    <button type="button" class="text-blue-500 hover:text-blue-600" 
                                            onclick="navigator.clipboard.writeText('{{ route('api.telegram.webhook', ['bot_uuid' => $telegramBot->uuid]) }}')">
                                        <flux:icon name="clipboard" class="size-4" />
                                    </button>
                                </div>
                                <flux:text size="xs" class="font-mono text-gray-500 break-all">{{ route('api.telegram.webhook', ['bot_uuid' => $telegramBot->uuid]) }}</flux:text>
                            </div>
                            
                            <flux:callout icon="check-circle" color="green">
                                <flux:callout.text>
                                    The webhook has been automatically set up. Your bot is ready to respond to messages that start with <span class="font-mono">{{ $telegramBot->command_prefix }}</span>.
                                </flux:callout.text>
                            </flux:callout>
                        </div>
                    </flux:card>
                @else
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg mt-4">
                        <div class="flex items-start gap-2">
                            <flux:icon name="exclamation-triangle" class="text-amber-500 mt-0.5" />
                            <div>
                                <flux:text class="font-medium">Setup incomplete</flux:text>
                                <flux:text size="sm" class="text-gray-600">You need to complete the Telegram bot setup to use this integration.</flux:text>
                                <div class="mt-2">
                                    <a href="{{ route('app.workspace.platforms.telegram.setup', ['uuid' => $workspace->uuid]) }}" class="inline-flex items-center gap-1 text-blue-500 hover:underline">
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
                <flux:icon name="chat-bubble-left-right" class="size-12 text-gray-400 mx-auto mb-3" />
                <flux:heading size="lg" class="mb-2">No Telegram Bot Connected</flux:heading>
                <flux:text class="text-gray-500 mb-6">Connect your Telegram bot to start receiving and responding to messages.</flux:text>
                <flux:button wire:click="setupTelegram" variant="primary" icon="plus" class="mx-auto">
                    Connect Telegram Bot
                </flux:button>
            </flux:card>
        @endif
    </div>
</div>
