<div class="container @container mx-auto space-y-4">
    <div class="flex flex-col items-start gap-4 @md:flex-row @md:items-center @md:justify-between">
        <flux:subheading>Your Email Inboxes</flux:subheading>
        <flux:modal.trigger name="create-inbox">
            <flux:button icon="inbox-stack">
                Create New Inbox
            </flux:button>
        </flux:modal.trigger>
    </div>

    <div class="grid grid-cols-1 gap-4 @lg:grid-cols-2 @2xl:grid-cols-3">
        @foreach($inboxes as $inbox)
            <flux:card class="group relative flex flex-col gap-4 hover:border-blue-500">
                <div class="flex justify-between">
                    <flux:heading>{{ $inbox->name }}</flux:heading>
                    <div class="flex gap-2">
                        @if($inbox->is_active)
                            <flux:badge variant="success">Active</flux:badge>
                        @else
                            <flux:badge variant="gray">Inactive</flux:badge>
                        @endif
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-zinc-500">{{ $inbox->username }}</p>
                </div>

                <div class="mt-auto flex justify-between">
                    <div class="flex gap-2">
                        <flux:button icon="envelope" link="{{ route('app.email.show', $inbox->id) }}" size="sm" variant="outline">
                            View Emails
                        </flux:button>
                    </div>
                    <div class="flex gap-2">
                        <flux:button icon="cog-8-tooth" link="{{ route('app.email.settings.index', $inbox->id) }}" size="sm" variant="outline">
                            Settings
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        @endforeach

        @if($inboxes->isEmpty())
            
                   
             No Email Inboxes Yet
                        Create your first email inbox to start having AI respond to your emails automatically.
                 
        @endif
    </div>
    
    <flux:modal name="create-inbox" class="md:w-[32rem]">
        <livewire:app.email.create />
    </flux:modal>
</div>