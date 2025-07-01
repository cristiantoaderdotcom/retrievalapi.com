<div>
    <div class="max-w-4xl mx-auto">
        <flux:card class="p-6 space-y-6">
            <div class="flex items-center gap-3 mb-2">
                <flux:icon name="envelope" class="text-blue-500 size-8" />
                <flux:heading size="xl">Setup your email inbox</flux:heading>
            </div>
            
            <flux:callout icon="information-circle" color="blue" class="mb-4">
                <flux:callout.heading>Connect your email account</flux:callout.heading>
                <flux:callout.text>
                    Set up your email inbox to send and receive emails directly from ReplyElf. All data is securely encrypted.
                </flux:callout.text>
            </flux:callout>

            <form wire:submit="create" class="space-y-6">
                <!-- Account Setup Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon name="user"  />
                        <flux:heading size="sm">Account Setup</flux:heading>
                    </div>
                    
                    <flux:input 
                        label="Inbox Name" 
                        wire:model="name" 
                        hint="A descriptive name to identify this inbox" 
                        placeholder="e.g. Support Inbox"
                        icon="inbox"
                    />
                    
                    <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                        <flux:input 
                            label="Email Address" 
                            type="email" 
                            wire:model="username" 
                            hint="Your email address" 
                            icon="at-symbol"
                            placeholder="your.email@example.com"
                        />
                        <flux:input 
                            label="Password" 
                            type="password" 
                            wire:model="password" 
                            hint="Email account password or app password" 
                            icon="key"
                        />
                    </div>
                </div>
                
                <flux:separator class="my-6" />
                
                <!-- Server Settings Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon name="server" />
                        <flux:heading size="sm">Server Settings</flux:heading>
                    </div>
                    
                    <div class="p-4 rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-2 mb-3">
                            <flux:badge color="blue" size="sm">IMAP</flux:badge>
                            <flux:text size="sm" >For receiving emails</flux:text>
                        </div>
                        <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                            <flux:input 
                                label="IMAP Host" 
                                wire:model="imap_host" 
                                placeholder="e.g. imap.domain.com" 
                                icon="server"
                            />
                            <flux:input 
                                label="IMAP Port" 
                                type="number" 
                                wire:model="imap_port" 
                                placeholder="Default: 993" 
                                icon="link"
                            />
                        </div>
                    </div>
                    
                    <div class="p-4 rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-2 mb-3">
                            <flux:badge color="green" size="sm">SMTP</flux:badge>
                            <flux:text size="sm" >For sending emails</flux:text>
                        </div>
                        <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                            <flux:input 
                                label="SMTP Host" 
                                wire:model="smtp_host" 
                                placeholder="e.g. smtp.domain.com" 
                                icon="server"
                            />
                            <flux:input 
                                label="SMTP Port" 
                                type="number" 
                                wire:model="smtp_port" 
                                placeholder="Default: 587" 
                                icon="link"
                            />
                        </div>
                    </div>
                </div>
                
                <flux:separator class="my-6" />
                
                <!-- Security Settings Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon name="shield-check" />
                        <flux:heading size="sm">Security Settings</flux:heading>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                        <flux:select 
                            label="Encryption" 
                            wire:model="encryption"
                            icon="lock-closed"
                            hint="Choose the encryption protocol for your email connection"
                        >
                            <option value="ssl">SSL</option>
                            <option value="tls">TLS</option>
                            <option value="none">None</option>
                        </flux:select>
                        
                        <div class="p-4 rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex items-center h-full">
                            <flux:checkbox 
                                label="Validate SSL Certificate" 
                                wire:model="validate_cert" 
                                hint="Recommended for security. Disable only if you encounter connection issues."
                            />
                        </div>
                    </div>
                </div>
                
                <flux:separator class="my-6" />
                
                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button variant="ghost" icon="x-mark">
                            Cancel
                        </flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" icon="check">
                        Create Inbox
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </div>
