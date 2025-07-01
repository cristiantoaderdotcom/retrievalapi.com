<div>
    <flux:card class="space-y-6">
        <div class="flex justify-between items-center">
            <flux:heading level="3">Product Feeds</flux:heading>
            <flux:button wire:click="create" variant="primary" icon="plus">Add Feed</flux:button>
        </div>
        
        <p class="text-zinc-600">
            Import products from e-commerce platforms and feed sources to create dynamic product listings for your chatbot.
        </p>
        
        <!-- Feed List -->
        <div class="mt-4">
            @if($feeds->isEmpty())
                <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-8 text-center">
                    <div class="flex justify-center mb-4">
                        <div class="bg-indigo-100 text-indigo-600 rounded-full p-3">
                            <flux:icon icon="shopping-bag" class="w-8 h-8" />
                        </div>
                    </div>
                    <h3 class="text-lg font-medium mb-2">No product feeds configured yet</h3>
                    <p class="text-zinc-500 mb-4">
                        Add your first product feed to automatically import products from your e-commerce store.
                    </p>
                    <flux:button wire:click="create" variant="primary" icon="plus">Add Your First Feed</flux:button>
                </div>
            @else
                <div class="overflow-hidden bg-white border border-zinc-200 sm:rounded-xl">
                    <ul role="list" class="divide-y divide-zinc-200">
                        @foreach($feeds as $feed)
                            <li class="p-4 sm:p-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-start gap-4">
                                        <div class="rounded-lg bg-indigo-100 p-2 flex-shrink-0">
                                            <flux:icon icon="{{ $adapters[$feed->platform]['icon'] ?? 'shopping-bag' }}" class="w-6 h-6 text-indigo-600" />
                                        </div>
                                        <div>
                                            <h4 class="text-base font-medium">{{ $feed->name }}</h4>
                                            <div class="mt-1 flex items-center gap-2 text-sm">
                                                <span class="text-zinc-500">{{ $adapters[$feed->platform]['name'] ?? 'Unknown' }}</span>
                                                <span class="text-zinc-300">•</span>
                                                <span class="text-zinc-500">{{ $feed->products_count }} products</span>
                                                <span class="text-zinc-300">•</span>
                                                <span class="text-zinc-500">Last sync: {{ $feed->last_sync_at ? $feed->last_sync_at->diffForHumans() : 'Never' }}</span>
                                            </div>
                                            @if($feed->sync_error)
                                                <div class="mt-2 text-sm text-red-600">
                                                    {{ $feed->sync_error }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <flux:badge color="{{ $feed->statusColor() }}">
                                            {{ $feed->statusLabel() }}
                                        </flux:badge>
                                        <div class="flex gap-2">
                                            <flux:button wire:click="syncFeed({{ $feed->id }})" 
                                                wire:loading.attr="disabled" 
                                                wire:target="syncFeed({{ $feed->id }})"
                                                size="sm" 
                                                icon="arrow-path"
                                                @class(['opacity-50 cursor-not-allowed' => $feed->isSyncing()])>
                                                Sync
                                            </flux:button>
                                            
                                            <flux:button wire:click="confirmDelete({{ $feed->id }})" 
                                                size="sm" 
                                                variant="ghost" 
                                                icon="trash"
                                                class="text-red-600 hover:text-red-700">
                                                Delete
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="mt-4">
                    {{ $feeds->links() }}
                </div>
            @endif
        </div>
    </flux:card>
    
    <!-- Create Feed Modal -->
    <flux:modal wire:model="showCreateModal" title="Add Product Feed">
        <form wire:submit="store">
            <div class="space-y-6">
                <flux:input wire:model="name" label="Feed Name" required placeholder="E.g., My Shopify Store Products" />

                <flux:select wire:model.live="platform" label="Platform">
                    @foreach($adapters as $key => $adapter)
                        <flux:select.option value="{{ $key }}">
                            <div class="flex items-center gap-2">
                                <flux:icon icon="{{ $adapter['icon'] }}" variant="micro" />
                                {{ $adapter['name'] }}
                            </div>
                        </flux:select.option>
                    @endforeach
                </flux:select>
                
                <p class="text-sm text-zinc-600">
                    {{ $adapters[$platform]['description'] ?? '' }}
                </p>
                
                <div x-data="{ showApiFields: false }" class="space-y-6">
                    <flux:checkbox wire:model.live="showCredentialsFields" label="Show API Credentials" description="Toggle to view or hide sensitive API credential fields" />
                    
                    <div x-show="$wire.showCredentialsFields" x-transition class="space-y-4 border border-zinc-200 rounded-lg p-4 bg-zinc-50">
                        <div class="text-sm font-medium text-zinc-800 mb-2">API Credentials</div>
                        
                        @foreach($currentAdapterFields as $field)
                            @php
                                $isCredential = in_array($field['name'], ['shop_domain', 'api_key', 'access_token']);
                                $modelName = $isCredential ? 'credentials.' . $field['name'] : 'configuration.' . $field['name'];
                            @endphp
                            
                            @if($isCredential)
                                @if($field['type'] === 'password')
                                    <flux:input 
                                        type="password" 
                                        wire:model="{{ $modelName }}" 
                                        label="{{ $field['label'] }}" 
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        description="{{ $field['help'] ?? '' }}"
                                        required="{{ $field['required'] ?? false }}" />
                                @else
                                    <flux:input 
                                        wire:model="{{ $modelName }}" 
                                        label="{{ $field['label'] }}" 
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        description="{{ $field['help'] ?? '' }}"
                                        required="{{ $field['required'] ?? false }}" />
                                @endif
                            @endif
                        @endforeach
                    </div>
                    
                    <div class="space-y-4 border border-zinc-200 rounded-lg p-4">
                        <div class="text-sm font-medium text-zinc-800 mb-2">Configuration Options</div>
                        
                        @foreach($currentAdapterFields as $field)
                            @php
                                $isCredential = in_array($field['name'], ['shop_domain', 'api_key', 'access_token']);
                                $modelName = $isCredential ? 'credentials.' . $field['name'] : 'configuration.' . $field['name'];
                            @endphp
                            
                            @if(!$isCredential)
                                @if($field['type'] === 'number')
                                    <flux:input 
                                        type="number" 
                                        wire:model="{{ $modelName }}" 
                                        label="{{ $field['label'] }}" 
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        description="{{ $field['help'] ?? '' }}"
                                        min="0"
                                        required="{{ $field['required'] ?? false }}" />
                                @else
                                    <flux:input 
                                        wire:model="{{ $modelName }}" 
                                        label="{{ $field['label'] }}" 
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        description="{{ $field['help'] ?? '' }}"
                                        required="{{ $field['required'] ?? false }}" />
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end gap-3">
                <flux:button wire:click="cancel" type="button">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Add Feed</flux:button>
            </div>
        </form>
    </flux:modal>
    
    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteModal" title="Confirm Delete">
        <div class="space-y-6">
            <p>Are you sure you want to delete this product feed? This will remove all associated configuration but won't delete any previously imported products.</p>
            
            <div class="flex justify-end gap-3">
                <flux:button wire:click="cancel" >Cancel</flux:button>
                <flux:button wire:click="delete" variant="primary" class="bg-red-600 hover:bg-red-700">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
