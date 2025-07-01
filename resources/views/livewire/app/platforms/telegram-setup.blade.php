<div>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <flux:icon name="chat-bubble-left-right" class="text-blue-500 size-8" />
                <flux:heading size="xl">Connect Telegram Bot</flux:heading>
            </div>
            
            <a href="{{ route('app.workspace.platforms.telegram', ['uuid' => $workspace->uuid]) }}" class="inline-flex items-center gap-1 text-gray-500 hover:text-gray-700">
                <flux:icon name="arrow-left" class="size-4" />
                <span>Back to Telegram</span>
            </a>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left column: Steps -->
            <div class="lg:col-span-1 space-y-6">
                <flux:card class="p-4 sticky top-6">
                    <flux:heading size="md" class="mb-4">Setup Guide</flux:heading>
                    
                    <div class="space-y-6">
                        <!-- Step 1 -->
                        <div class="relative pl-8 pb-6 border-l border-gray-200 dark:border-gray-800">
                            <div class="absolute left-0 top-0 flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 -translate-x-1/2">
                                <flux:text size="sm" class="font-semibold text-blue-600">1</flux:text>
                            </div>
                            <flux:heading size="sm" class="mb-2">Create Telegram Bot</flux:heading>
                            <flux:text size="sm" class="">
                                Create a new bot with BotFather on Telegram
                            </flux:text>
                        </div>
                        
                        <!-- Step 2 -->
                        <div class="relative pl-8 pb-6 border-l border-gray-200 dark:border-gray-800">
                            <div class="absolute left-0 top-0 flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 -translate-x-1/2">
                                <flux:text size="sm" class="font-semibold text-blue-600">2</flux:text>
                            </div>
                            <flux:heading size="sm" class="mb-2">Get Bot Token</flux:heading>
                            <flux:text size="sm" class="">
                                Copy the HTTP API token from BotFather
                            </flux:text>
                        </div>
                        
                        <!-- Step 3 -->
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-0 flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 -translate-x-1/2">
                                <flux:text size="sm" class="font-semibold text-blue-600">3</flux:text>
                            </div>
                            <flux:heading size="sm" class="mb-2">Complete Setup</flux:heading>
                            <flux:text size="sm" class="">
                                Save your bot details and we'll automatically connect it
                            </flux:text>
                        </div>
                    </div>
                </flux:card>
            </div>
            
            <!-- Right column: Setup form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Create Telegram Bot -->
                <flux:card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:badge color="blue">Step 1</flux:badge>
                        <flux:heading size="md">Create a Telegram Bot</flux:heading>
                    </div>
                    
                    <ol class="ml-5 list-decimal space-y-3">
                        <li>Open Telegram and search for <a href="https://t.me/BotFather" target="_blank" class="text-blue-500 hover:underline font-medium">@BotFather</a></li>
                        <li>Send the command <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">/newbot</span> to start creating a new bot</li>
                        <li>Enter a name for your bot (e.g., "My AI Assistant")</li>
                        <li>Enter a unique username for your bot (must end with "bot", e.g., "my_ai_assistant_bot")</li>
                    </ol>
                </flux:card>
                
                <!-- Get Bot Token -->
                <flux:card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:badge color="blue">Step 2</flux:badge>
                        <flux:heading size="md">Get Bot Token</flux:heading>
                    </div>
                    
                    <ol class="ml-5 list-decimal space-y-3">
                        <li>Once your bot is created, BotFather will send you a message with your bot's token</li>
                        <li>The token looks like <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">123456789:ABCDefGhIJKlmNoPQRsTUVwxyZ</span></li>
                        <li>Copy this token - you'll need it for the setup form below</li>
                        <li>This token is like a password, don't share it publicly</li>
                    </ol>
                </flux:card>
                
                <!-- Set Commands -->
                <flux:card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:badge color="blue">Step 3</flux:badge>
                        <flux:heading size="md">Set Bot Commands</flux:heading>
                    </div>
                    
                    <ol class="ml-5 list-decimal space-y-3">
                        <li>Send the command <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">/setcommands</span> to BotFather</li>
                        <li>Select the bot you just created</li>
                        <li>Send the following text to define the command used to ask the AI questions:</li>
                    </ol>
                    
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mt-4 mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <flux:text size="sm" class="font-semibold">Bot Commands:</flux:text>
                            <button type="button" class="text-blue-500 hover:text-blue-600 p-1" 
                                    onclick="navigator.clipboard.writeText('ask - Ask a question to the AI')">
                                <flux:icon name="clipboard" class="size-4" />
                            </button>
                        </div>
                        <flux:text class="font-mono text-sm">ask - Ask a question to the AI</flux:text>
                    </div>
                    
                    <flux:callout icon="information-circle" color="blue" class="mt-4">
                        <flux:callout.text>
                            You need to create these commands for your Telegram bot so users can see them in the menu and understand how to interact with the bot.
                        </flux:callout.text>
                    </flux:callout>
                </flux:card>
                
                <!-- Bot Information Form -->
                <flux:card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:badge color="green">Final Step</flux:badge>
                        <flux:heading size="md">Complete Telegram Bot Setup</flux:heading>
                    </div>
                    
                    <form wire:submit="update" class="space-y-6">
                        <flux:input 
                            label="Bot Username" 
                            wire:model="bot_username" 
                            hint="Your Telegram bot username (e.g., my_ai_assistant_bot)" 
                            placeholder="my_ai_assistant_bot"
                            icon="identification"
                            required
                        />
                        
                        <flux:input 
                            label="Bot Token" 
                            wire:model="bot_token" 
                            hint="The HTTP API token provided by BotFather" 
                            placeholder="123456789:ABCDefGhIJKlmNoPQRsTUVwxyZ"
                            icon="key"
                            required
                        />
                        
                        <flux:input 
                            label="Command Prefix" 
                            wire:model="command_prefix" 
                            hint="The command users will type to interact with the AI" 
                            placeholder="/ask"
                            icon="command-line"
                            required
                        />
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <flux:heading size="sm" class="mb-3">How It Works</flux:heading>
                            
                            <flux:text size="sm" class=" mb-4">
                                When users send a message to your bot with the command prefix (e.g., "/ask What is Laravel?"), 
                                the bot will process the question and respond with an AI-generated answer. 
                                Messages without the command prefix will be ignored.
                            </flux:text>
                            
                            <flux:callout icon="information-circle" color="blue">
                                <flux:callout.text>
                                    When you save this form, we'll automatically configure the webhook for your bot.
                                    You don't need to perform any additional setup steps.
                                </flux:callout.text>
                            </flux:callout>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <a href="{{ route('app.workspace.platforms.telegram', ['uuid' => $workspace->uuid]) }}">
                                <flux:button variant="ghost" icon="x-mark">
                                    Cancel
                                </flux:button>
                            </a>
                            <flux:button type="submit" variant="primary" icon="check">
                                Complete Setup
                            </flux:button>
                        </div>
                    </form>
                </flux:card>
            </div>
        </div>
    </div>
</div>
