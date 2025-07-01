<div class="container @container mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <flux:button icon="arrow-left" link="{{ route('app.email.show', $inbox->id) }}" size="xs" variant="ghost" />
            <flux:heading size="lg">{{ $inbox->name }} Settings</flux:heading>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 @lg:grid-cols-3">
        <div class="@lg:col-span-2">
            <flux:card>
                <form wire:submit="save" class="space-y-6">
                    <div>
                        <flux:subheading>Connection Settings</flux:subheading>
                        <p class="text-sm text-zinc-500">Configure your email inbox connection settings.</p>
                    </div>

                    <flux:separator />

                    <div>
                        <flux:input label="Inbox Name" wire:model="name" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                        <flux:input label="IMAP Host" wire:model="host" />
                        <flux:input label="IMAP Port" type="number" wire:model="port" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                        <flux:select label="Encryption" wire:model="encryption">
                            <option value="ssl">SSL</option>
                            <option value="tls">TLS</option>
                            <option value="none">None</option>
                        </flux:select>
                        
                        <div class="flex flex-col justify-end">
                            <flux:checkbox label="Validate SSL Certificate" wire:model="validate_cert" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                        <flux:input label="Email Address" type="email" wire:model="username" />
                        <flux:input label="Password" type="password" wire:model="password" hint="Leave empty to keep current password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <flux:subheading>Status</flux:subheading>
                            <p class="text-sm text-zinc-500">Enable or disable automatic email processing.</p>
                        </div>
                        <flux:toggle wire:model="is_active" />
                    </div>

                    <div class="flex justify-end">
                        <flux:button type="submit">
                            Save Settings
                        </flux:button>
                    </div>
                </form>
            </flux:card>

            <flux:card class="mt-6">
                <div class="space-y-4">
                    <div>
                        <flux:subheading class="text-red-500">Danger Zone</flux:subheading>
                        <p class="text-sm text-zinc-500">Permanently delete this inbox and all associated data.</p>
                    </div>

                    <flux:separator />

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium">Delete this inbox</p>
                            <p class="text-sm text-zinc-500">This action cannot be undone.</p>
                        </div>
                        <flux:button 
                            x-on:click="confirm('Are you sure you want to delete this inbox? This action cannot be undone.') && $wire.deleteInbox()"
                            variant="danger">
                            Delete Inbox
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        </div>

        <div>
            <flux:card>
                <flux:subheading>Inbox Information</flux:subheading>
                <flux:separator />
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-zinc-500">Status</p>
                        <p class="font-medium">{{ $is_active ? 'Active' : 'Inactive' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-zinc-500">Email Address</p>
                        <p class="font-medium">{{ $username }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-zinc-500">Server</p>
                        <p class="font-medium">{{ $host }}:{{ $port }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-zinc-500">Encryption</p>
                        <p class="font-medium">{{ strtoupper($encryption) }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-zinc-500">Created</p>
                        <p class="font-medium">{{ $inbox->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>