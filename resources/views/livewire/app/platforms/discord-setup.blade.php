<div>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <flux:icon name="chat-bubble-left-right" class="text-indigo-500 size-8" />
                <flux:heading size="xl">Set Up Discord Integration</flux:heading>
            </div>
            
            <a href="{{ route('app.workspace.platforms.discord', ['uuid' => $workspace->uuid]) }}" class="inline-flex items-center gap-1 text-gray-500 hover:text-gray-700">
                <flux:icon name="arrow-left" class="size-4" />
                <span>Back</span>
            </a>
        </div>
        
        <flux:card class="mb-6 p-6">
            <div class="prose max-w-none">
                <flux:heading size="lg" class="mb-3">Connect Your AI Assistant to Discord</flux:heading>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Add our AI assistant to your Discord server and let it respond to questions from your server members.
                    Once connected, your members can use a custom slash command to get instant AI-powered responses based on your knowledge base.
                </p>
                
                <div class="flex items-center mt-5">
                    <a href="https://discord.com/oauth2/authorize?client_id=1369918518459498566&integration_type=0&scope=bot%20applications.commands&permissions=19327363072" 
                       target="_blank" 
                       class="inline-flex items-center px-5 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.14 96.36" class="w-6 h-6 mr-2 fill-current">
                            <path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.25,60,73.25,53s5-12.74,11.44-12.74S96.23,46,96.12,53,91.08,65.69,84.69,65.69Z"/>
                        </svg>
                        Add to Discord
                    </a>
                </div>
            </div>
        </flux:card>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left column: Steps -->
            <div class="lg:col-span-1 space-y-6">
                <flux:card class="p-4 sticky top-6">
                    <flux:heading size="md" class="mb-4">Setup Guide</flux:heading>
                    
                    <div class="space-y-6">
                        <!-- Step 1 -->
                        <div class="relative pl-8 pb-6 border-l border-gray-200 dark:border-gray-800">
                            <div class="absolute left-0 top-0 flex items-center justify-center w-6 h-6 rounded-full bg-blue-100  -translate-x-1/2">
                                <flux:text size="sm" class="font-semibold text-blue-600">1</flux:text>
                            </div>
                            <flux:heading size="sm" class="mb-2">Add Bot to Your Server</flux:heading>
                            <flux:text size="sm" class="">
                                Add our AI assistant bot to your Discord server
                            </flux:text>
                        </div>
                        
                        <!-- Step 2 -->
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-0 flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 -translate-x-1/2">
                                <flux:text size="sm" class="font-semibold text-blue-600">2</flux:text>
                            </div>
                            <flux:heading size="sm" class="mb-2">Configure Command</flux:heading>
                            <flux:text size="sm" class="">
                                Set up your custom command prefix for your server
                            </flux:text>
                        </div>
                    </div>
                </flux:card>
            </div>
            
            <!-- Right column: Setup form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Add Bot to Server -->
                <flux:card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:badge color="blue">Step 1</flux:badge>
                        <flux:heading size="md">Add Bot to Your Server</flux:heading>
                    </div>
                    
                    <ol class="ml-5 list-decimal space-y-3 ">
                        <li>Click on the <a href="https://discord.com/oauth2/authorize?client_id=1369918518459498566&integration_type=0&scope=bot%20applications.commands&permissions=19327363072" target="_blank" class="text-blue-500 hover:underline font-medium">Authorize Bot</a> link to add our AI assistant to your Discord server</li>
                        <li>Select the server where you want to add the bot and click "Authorize"</li>
                        <li>Complete any verification steps required by Discord</li>
                        <li>Once added, the bot will automatically be available in your server</li>
                    </ol>
                    
                    <flux:callout icon="information-circle" color="blue" class="mt-4">
                        <flux:callout.text>
                            You must have the "Manage Server" permission in your Discord server to add the bot.
                        </flux:callout.text>
                    </flux:callout>
                </flux:card>
                
                <!-- Bot Information Form -->
                <flux:card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:badge color="blue">Step 2</flux:badge>
                        <flux:heading size="md">Configure Your Bot</flux:heading>
                    </div>
                    
                    <form wire:submit="update" class="space-y-6">
                        <flux:input 
                            label="Guild ID (Server ID)" 
                            wire:model="guild_id" 
                            hint="Your Discord server ID - required to identify your server" 
                            placeholder="9876543210987654321"
                            icon="building-storefront"
                            required
                        />
                        
                        <flux:input 
                            label="Command Prefix" 
                            wire:model="command_prefix" 
                            hint="The slash command users will type to interact with the AI (without the /)" 
                            placeholder="ask"
                            icon="command-line"
                            required
                        />
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <flux:heading size="sm" class="mb-3">How to Find Your Server ID</flux:heading>
                            
                            <ol class="ml-5 list-decimal space-y-3 ">
                                <li>Open Discord and go to Settings</li>
                                <li>Under App Settings, click on "Advanced"</li>
                                <li>Enable "Developer Mode"</li>
                                <li>Return to your server, right-click on the server name at the top-left</li>
                                <li>Select "Copy ID" from the menu</li>
                                <li>Paste this ID in the Guild ID field above</li>
                            </ol>
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <flux:heading size="sm" class="mb-3">How It Works</flux:heading>
                            
                            <flux:text size="sm" class=" mb-4">
                                When users enter the slash command in your Discord server (e.g., "/ask What is Laravel?"), 
                                the bot will process the question and respond with an AI-generated answer.
                            </flux:text>
                            
                            <flux:callout icon="information-circle" color="blue">
                                <flux:callout.text>
                                    When you save this form, we'll automatically register the slash command with Discord.
                                    The command will be available immediately in your server.
                                </flux:callout.text>
                            </flux:callout>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <a href="{{ route('app.workspace.platforms.discord', ['uuid' => $workspace->uuid]) }}">
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
