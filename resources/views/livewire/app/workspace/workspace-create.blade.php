<div>
    <flux:modal class="md:w-96" name="create-workspace">
        <div class="space-y-6">
            <div>
                <flux:heading size="xl">Workspace</flux:heading>
                <flux:subheading>
                    Create a new workspace 
                </flux:subheading>
            </div>
    
            <form wire:submit="store" class="space-y-6">
                <flux:input wire:model="name" label="Name" description="This name is for reference only." />
    
                <flux:select label="Language" searchable variant="listbox" wire:model="language_id">
                    @foreach ($languages as $language)
                        <flux:select.option value="{{ $language->id }}">
                            <div class="flex items-center gap-2">
                                <img alt="{{ $language->name }}" class="max-w-5" src="{{ asset('assets/icons/languages/' . $language->code . '.svg') }}" />
                                {{ $language->name }}
                            </div>
                        </flux:select.option>
                    @endforeach
                </flux:select>
    
                <flux:button type="submit" icon="plus" variant="primary">Create Workspace</flux:button>
            </form>
        </div>
    </flux:modal>
</div>
