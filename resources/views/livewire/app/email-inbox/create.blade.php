<flux:modal class="md:w-96" name="create-inbox">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create New Email Inbox</flux:heading>
            <flux:subheading>Configure your email account for AI responses.</flux:subheading>
        </div>
        
        <form wire:submit="create" class="space-y-4">
            <flux:input label="Inbox Name" wire:model="name" hint="A name to identify this inbox" />
            
            <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                <flux:input label="IMAP Host" wire:model="host" hint="e.g. imap.gmail.com" />
                <flux:input label="IMAP Port" type="number" wire:model="port" hint="Default: 993" />
            </div>
            
            <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                <flux:select label="Encryption" wire:model="encryption">
                    <option value="ssl">SSL</option>
                    <option value="tls">TLS</option>
                    <option value="none">None</option>
                </flux:select>
                
                <flux:checkbox label="Validate SSL Certificate" wire:model="validate_cert" />
            </div>
            
            <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                <flux:input label="Email Address" type="email" wire:model="username" hint="Your email address" />
                <flux:input label="Password" type="password" wire:model="password" hint="Email account password or app password" />
            </div>
            
            <div class="mt-6 flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="outline">
                        Cancel
                    </flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Create Inbox
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>
