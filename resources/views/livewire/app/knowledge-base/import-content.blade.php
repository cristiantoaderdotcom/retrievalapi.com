<div>
    <flux:tab.group>
        <div class="flex items-center gap-3">
            <flux:tabs variant="segmented">
                <flux:tab name="links" icon="link" wire:click="changeTab('links')">Websites</flux:tab>
                <flux:tab name="files" icon="clipboard-document" wire:click="changeTab('files')">Files</flux:tab>
                <flux:tab name="texts" icon="document-text" wire:click="changeTab('texts')">Texts</flux:tab>
                <flux:tab name="videos" icon="play-circle" wire:click="changeTab('videos')">Videos</flux:tab>
                {{-- <flux:tab name="products" icon="shopping-bag" wire:click="changeTab('products')">Products</flux:tab> --}}
            </flux:tabs>

            <flux:icon.loading wire:loading variant="mini"/>
        </div>

        <flux:tab.panel name="links">
            @if($this->tab === 'links')
                <livewire:app.knowledge-base.resources.links :workspace="$workspace" />
            @else
                <flux:card class="min-h-52">
                    <div class="animate-pulse flex flex-col space-y-6">
                        <div class="flex-1 space-y-3">
                            <div class="w-16 h-2 bg-zinc-300 rounded-xs"></div>
                            <div class="h-2 bg-zinc-300 rounded-xs"></div>
                            <div class="w-36 h-2 bg-zinc-300 rounded-xs"></div>

                            <div class="grid grid-cols-4 gap-4">
                                <div class="h-8 bg-zinc-300 rounded-xs col-span-3"></div>
                                <div class="h-8 bg-zinc-300 rounded-xs col-span-1"></div>
                            </div>
                        </div>

                        <div class="flex-1 space-y-3">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="h-14 bg-zinc-300 rounded-xs col-span-1"></div>
                                <div class="h-14 bg-zinc-300 rounded-xs col-span-1"></div>
                                <div class="h-14 bg-zinc-300 rounded-xs col-span-1"></div>
                            </div>
                        </div>
                    </div>
                </flux:card>
            @endif
        </flux:tab.panel>

        <flux:tab.panel name="files">
            @if($this->tab === 'files')
                <livewire:app.knowledge-base.resources.files :workspace="$workspace" />
            @else
                <flux:card>
                    <div class="animate-pulse flex flex-col space-y-6">
                        <div class="flex-1 space-y-3">
                            <div class="w-12 h-2 bg-zinc-300 rounded-xs"></div>
                            <div class="w-72 h-2 bg-zinc-300 rounded-xs"></div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div class="w-24 h-2 bg-zinc-300 rounded-xs"></div>
                            <div class="flex items-center gap-4">
                                <div class="w-32 h-8 bg-zinc-300 rounded-xs"></div>
                                <div class="w-16 h-2 bg-zinc-300 rounded-xs"></div>
                            </div>
                        </div>
                    </div>
                </flux:card>
            @endif
        </flux:tab.panel>

        <flux:tab.panel name="videos">
            @if($this->tab === 'videos')
                <livewire:app.knowledge-base.resources.videos :workspace="$workspace" />
            @else
                <flux:card>
                    <div class="animate-pulse flex flex-col space-y-6">
                        <div class="flex-1 space-y-3">
                            <div class="w-12 h-2 bg-zinc-300 rounded-xs"></div>
                            <div class="w-72 h-2 bg-zinc-300 rounded-xs"></div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div class="w-24 h-2 bg-zinc-300 rounded-xs"></div>
                            <div class="flex items-center gap-4">
                                <div class="w-32 h-8 bg-zinc-300 rounded-xs"></div>
                                <div class="w-16 h-2 bg-zinc-300 rounded-xs"></div>
                            </div>
                        </div>
                    </div>
                </flux:card>
            @endif
        </flux:tab.panel>

        <flux:tab.panel name="texts">
            @if($this->tab === 'texts')
                <livewire:app.knowledge-base.resources.texts :workspace="$workspace" />
            @else
                <flux:card>
                    <div class="animate-pulse flex flex-col space-y-6">
                        <div class="flex-1 space-y-3">
                            <div class="w-12 h-2 bg-zinc-300 rounded-xs"></div>
                            <div class="w-72 h-2 bg-zinc-300 rounded-xs"></div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div class="w-24 h-2 bg-zinc-300 rounded-xs"></div>
                            <div class="flex items-center gap-4">
                                <div class="w-32 h-8 bg-zinc-300 rounded-xs"></div>
                                <div class="w-16 h-2 bg-zinc-300 rounded-xs"></div>
                            </div>
                        </div>
                    </div>
                </flux:card>
            @endif
        </flux:tab.panel>

        <flux:tab.panel name="products">
            @if($this->tab === 'products')
                <livewire:app.knowledge-base.resources.products :workspace="$workspace" />
            @else
                <flux:card>
                    <div class="animate-pulse flex flex-col space-y-6">
                        <div class="flex-1 space-y-3">
                            <div class="w-12 h-2 bg-zinc-300 rounded-xs"></div>
                            <div class="w-72 h-2 bg-zinc-300 rounded-xs"></div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <div class="w-24 h-2 bg-zinc-300 rounded-xs"></div>
                                    <div class="h-8 bg-zinc-300 rounded-xs"></div>
                                    <div class="w-36 h-2 bg-zinc-300 rounded-xs"></div>
                                    <div class="h-24 bg-zinc-300 rounded-xs"></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="w-24 h-2 bg-zinc-300 rounded-xs"></div>
                                    <div class="h-32 bg-zinc-300 rounded-xs"></div>
                                    <div class="w-32 h-2 bg-zinc-300 rounded-xs"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-32 h-8 bg-zinc-300 rounded-xs"></div>
                            </div>
                        </div>
                    </div>
                </flux:card>
            @endif
        </flux:tab.panel>
    </flux:tab.group>
</div>