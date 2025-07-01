<div>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <flux:icon name="chat-bubble-left-right" class="text-blue-500 size-8" />
                <flux:heading size="xl">Connect Facebook Page</flux:heading>
            </div>
            
            <a href="{{ route('app.workspace.platforms.facebook', ['uuid' => $workspace->uuid]) }}" class="inline-flex items-center gap-1 text-gray-500 hover:text-gray-700">
                <flux:icon name="arrow-left" class="size-4" />
                <span>Back to Facebook</span>
            </a>
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
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left column: Steps -->
            <div class="lg:col-span-1 space-y-6">
                <flux:card class="p-4 sticky top-6">
                    <flux:heading size="md" class="mb-4">Setup Guide</flux:heading>
                    
                    <div class="space-y-6">
                        <!-- Step 1 -->
                        <div class="relative pl-8 pb-6 border-l border-gray-200 dark:border-gray-800">
                            <div class="absolute left-0 top-0 flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 -translate-x-1/2">
                                <flux:text size="sm" class="font-semibold text-green-600">1</flux:text>
                            </div>
                            <flux:heading size="sm" class="mb-2">Create Facebook App</flux:heading>
                            <flux:text size="sm" class="">
                                Create a new app on Facebook for Developers portal with Business type
                            </flux:text>
                        </div>
                        
                        <!-- Step 2 -->
                        <div class="relative pl-8 pb-6 border-l border-gray-200 dark:border-gray-800">
                            <div class="absolute left-0 top-0 flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 -translate-x-1/2">
                                <flux:text size="sm" class="font-semibold text-green-600">2</flux:text>
                            </div>
                            <flux:heading size="sm" class="mb-2">Add Messenger Product</flux:heading>
                            <flux:text size="sm" class="">
                                Add Messenger product and connect your Facebook page
                            </flux:text>
                        </div>
                        
                        <!-- Step 3 -->
                        <div class="relative pl-8 pb-6 border-l border-gray-200 dark:border-gray-800">
                            <div class="absolute left-0 top-0 flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 -translate-x-1/2">
                                <flux:text size="sm" class="font-semibold text-green-600">3</flux:text>
                            </div>
                            <flux:heading size="sm" class="mb-2">Setup Webhook</flux:heading>
                            <flux:text size="sm" class="">
                                Configure webhook with the provided URL and token
                            </flux:text>
                        </div>
                        
                        <!-- Step 4 -->
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-0 flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 -translate-x-1/2">
                                <flux:text size="sm" class="font-semibold text-green-600">4</flux:text>
                            </div>
                            <flux:heading size="sm" class="mb-2">Complete Setup</flux:heading>
                            <flux:text size="sm" class="">
                                Save your page details to enable Facebook integration
                            </flux:text>
                        </div>
                    </div>
                </flux:card>
            </div>
            
            <!-- Right column: Setup form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Facebook Developer Account -->
                <flux:card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:badge color="blue">Step 1</flux:badge>
                        <flux:heading size="md">Create a Facebook Developer Account</flux:heading>
                    </div>
                    
                    <ol class="ml-5 list-decimal space-y-3">
                        <li>Go to <a href="https://developers.facebook.com" target="_blank" class="text-blue-500 hover:underline font-medium">Facebook for Developers</a></li>
                        <li>Click on "My Apps" and create a new app with the "Business" type</li>
                        <li>Complete the app creation process with a name and contact email</li>
                    </ol>
                </flux:card>
                
                <!-- Configure Messenger -->
                <flux:card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:badge color="blue">Step 2</flux:badge>
                        <flux:heading size="md">Configure Messenger</flux:heading>
                    </div>
                    
                    <ol class="ml-5 list-decimal space-y-3">
                        <li>In your app dashboard, click "Add Product" and select "Messenger"</li>
                        <li>Go to "Messenger > Settings"</li>
                        <li>Under "Access Tokens", connect your Facebook Page</li>
                        <li>Copy the generated Page Access Token for later use</li>
                    </ol>
                </flux:card>
                
                <!-- Setup Webhook -->
                <flux:card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:badge color="blue">Step 3</flux:badge>
                        <flux:heading size="md">Setup Webhook</flux:heading>
                    </div>
                    
                    <flux:text class="mb-4">In Messenger Settings, scroll to "Webhooks" and click "Add Callback URL". Use the following details:</flux:text>
                    
                    <div class="space-y-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center mb-1">
                                <flux:text size="sm" class="font-semibold">Callback URL:</flux:text>
                                <button type="button" class="text-blue-500 hover:text-blue-600 p-1" 
                                        onclick="navigator.clipboard.writeText('{{ route('api.facebook.webhook', ['page_uuid' => $facebookPage->uuid]) }}')">
                                    <flux:icon name="clipboard" class="size-4" />
                                </button>
                            </div>
                            <flux:text class="font-mono text-sm break-all">{{ route('api.facebook.webhook', ['page_uuid' => $facebookPage->uuid]) }}</flux:text>
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center mb-1">
                                <flux:text size="sm" class="font-semibold">Verify Token:</flux:text>
                                <button type="button" class="text-blue-500 hover:text-blue-600 p-1" 
                                        onclick="navigator.clipboard.writeText('{{ $facebookPage->page_verify_token }}')">
                                    <flux:icon name="clipboard" class="size-4" />
                                </button>
                            </div>
                            <flux:text class="font-mono text-sm break-all">{{ $facebookPage->page_verify_token }}</flux:text>
                        </div>
                    </div>
                    
                    <flux:callout icon="information-circle" color="blue" class="mt-4">
                        <flux:callout.text>
                            Under "Webhook Fields", make sure to subscribe to: <span class="font-medium">messages, messaging_postbacks, messaging_optins</span>
                        </flux:callout.text>
                    </flux:callout>
                </flux:card>
                
                <!-- Page Information Form -->
                <flux:card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:badge color="green">Step 4</flux:badge>
                        <flux:heading size="md">Complete Facebook Page Setup</flux:heading>
                    </div>
                    
                    <form wire:submit="update" class="space-y-6">
                        <flux:input 
                            label="Page Name" 
                            wire:model="page_name" 
                            hint="Name of your Facebook page" 
                            placeholder="My Business Page"
                            icon="identification"
                            required
                        />
                        
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <flux:input 
                                label="Page ID" 
                                wire:model="page_id" 
                                hint="Your Facebook Page ID" 
                                placeholder="123456789012345"
                                icon="identification"
                                required
                            />
                            <flux:input 
                                label="Page Access Token" 
                                wire:model="page_access_token" 
                                hint="Token generated from Facebook Developer Dashboard" 
                                icon="key"
                                required
                            />
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <flux:heading size="sm" class="mb-3">Integration Options</flux:heading>
                            
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <flux:checkbox 
                                    label="Handle Messenger Messages" 
                                    wire:model="handle_messages" 
                                    hint="Respond to messages sent to your page via Messenger"
                                />
                                <flux:checkbox 
                                    label="Handle Page Comments" 
                                    wire:model="handle_comments" 
                                    hint="Respond to comments on your page posts"
                                />
                            </div>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <a href="{{ route('app.workspace.platforms.facebook', ['uuid' => $workspace->uuid]) }}">
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
