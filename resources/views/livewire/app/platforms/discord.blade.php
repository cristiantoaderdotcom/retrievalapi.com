<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="sm:flex sm:items-start sm:justify-between">
            <div>
                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.14 96.36" class="w-5 h-5 mr-2 text-indigo-400 fill-current">
                            <path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.25,60,73.25,53s5-12.74,11.44-12.74S96.23,46,96.12,53,91.08,65.69,84.69,65.69Z"/>
                        </svg>
                        Discord
                    </div>
                </h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                    <p>Connect your Discord bot to provide AI-powered responses to users in your Discord server.</p>
                </div>
            </div>
            <div class="mt-5 sm:ml-6 sm:mt-0 sm:flex sm:flex-shrink-0 sm:items-center">
                @if (!$discordBot)
                    <button type="button" wire:click="setupDiscord" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Connect Discord Bot
                    </button>
                @else
                    <button type="button" wire:click="setupDiscord" class="inline-flex items-center rounded-md bg-green-100 px-3 py-2 text-sm font-semibold text-green-700 shadow-sm hover:bg-green-200">
                        Update Discord Bot
                    </button>
                @endif
            </div>
        </div>
        
        @if ($discordBot)
            <div class="mt-5 border-t border-gray-200 dark:border-gray-700 pt-5">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Bot Details</h4>
                
                <div class="mt-3 divide-y divide-gray-200 dark:divide-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-2">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Bot Username</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $discordBot->bot_username }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Command Prefix</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $discordBot->command_prefix }}</p>
                        </div>
                    </div>
                    
                    <div class="py-3">
                        <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400">Usage Examples</h5>
                        <div class="mt-1 p-2 bg-gray-50 dark:bg-gray-700 rounded text-xs font-mono overflow-x-auto">
                            <p>/{!! $discordBot->command_prefix !!} How do I use this bot?</p>
                            <p class="mt-1">/{!! $discordBot->command_prefix !!} Tell me about your company.</p>
                        </div>
                    </div>
                    
                    <div class="py-3">
                        <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400">Last Activity</h5>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            @if ($discordBot->last_message_at)
                                {{ $discordBot->last_message_at->diffForHumans() }}
                            @else
                                No recent activity
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
