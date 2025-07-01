<div x-data="{
        selected: $wire.entangle('selected'),
        get selectedCount() {
            return Object.values(this.selected).filter(value => value).length;
        },
        get visible() {
            return this.selectedCount > 0;
        },
    }">
    <flux:card>
        <flux:checkbox.group class="select-auto!">
            <form wire:submit="filter">
                <div x-data="{ filtering: false }" class="rounded-t-lg border border-zinc-200 border-b-zinc-300 bg-white p-3">
                    <div class="flex items-center flex-wrap gap-3">
                        <div class="flex items-center">
                            <flux:input.group>
                                <flux:select wire:model="filters.match" variant="listbox" size="sm" class="max-w-fit">
                                    <flux:select.option value="contains" selected>Contains</flux:select.option>
                                    <flux:select.option value="not_contains">Does not contain</flux:select.option>
                                    <flux:select.option value="starts">Starts with</flux:select.option>
                                    <flux:select.option value="ends">Ends with</flux:select.option>
                                </flux:select>

                                <flux:input wire:model="filters.search" placeholder="Search..." size="sm"/>
                            </flux:input.group>
                        </div>
                        <div class="flex items-center">
                            <flux:select wire:model="filters.status" variant="listbox" size="sm" class="max-w-fit">
                                <flux:select.option value="" selected>All Statuses</flux:select.option>
                                <flux:select.option value="processed">
                                    <div class="flex items-center gap-2">
                                        <flux:icon icon="check-circle" variant="micro" class="text-lime-500" /> Processed
                                    </div>
                                </flux:select.option>
                                <flux:select.option value="processing">
                                    <div class="flex items-center gap-2">
                                        <flux:icon icon="clock" variant="micro" class="text-amber-400" /> Processing
                                    </div>
                                </flux:select.option>
                                <flux:select.option value="failed">
                                    <div class="flex items-center gap-2">
                                        <flux:icon icon="exclamation-circle" variant="micro" class="text-red-400" /> Failed
                                    </div>
                                </flux:select.option>
                                <flux:select.option value="hidden">
                                    <div class="flex items-center gap-2">
                                        <flux:icon icon="eye-slash" variant="micro" class="text-zinc-400" /> Hidden
                                    </div>
                                </flux:select.option>
                            </flux:select>
                        </div>
                        <div class="flex items-center gap-3">
                            <flux:button type="submit" size="sm">Filter</flux:button>

                            @if(!$this->filterExists)
                                <flux:button wire:click="resetFilters" size="sm" variant="ghost">Clear all</flux:button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>

            <div class="[&>div>div:nth-of-type(1)]:rounded-t-none [&_table]:bg-white">
                <flux:table :paginate="$resources">
                    <flux:table.columns>
                        <flux:table.column class="w-11">
                            <flux:checkbox.all />
                        </flux:table.column>
                        <flux:table.column>Product</flux:table.column>
                        <flux:table.column>Price</flux:table.column>
                        <flux:table.column>Tags / Categories</flux:table.column>
                        <flux:table.column>Words</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse($resources as $resource)
                            <flux:table.row wire:key="row-{{ $resource->id }}">
                                <flux:table.cell>
                                    <div class="flex items-center justify-center">
                                        @if($resource->process_started_at && $resource->status?->isProcessing())
                                            <flux:icon.loading variant="mini"/>
                                        @else
                                            <flux:checkbox
                                                wire:model="selected.{{ $resource->id }}"
                                                @class([
                                                    'opacity-20' => $resource->process_completed_at && !$resource->status?->isFailed(),
                                                ])
                                            />
                                        @endif
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-4">
                                        @if($resource->resourceable->image_path)
                                            <img src="{{ asset($resource->resourceable->image_path) }}" 
                                                alt="{{ $resource->resourceable->name }}" 
                                                class="w-12 h-12 object-cover rounded-md border border-zinc-200" />
                                        @else
                                            <div class="w-12 h-12 bg-zinc-100 flex items-center justify-center rounded-md border border-zinc-200">
                                                <flux:icon icon="photo" class="text-zinc-400" />
                                            </div>
                                        @endif
                                        <div class="flex flex-col w-48 sm:w-72 whitespace-normal">
                                            <div class="font-medium">
                                                {{ $resource->resourceable->name }}
                                            </div>
                                            @if($resource->resourceable->description)
                                                <div class="text-xs text-zinc-500 line-clamp-2">
                                                    {{ $resource->resourceable->description }}
                                                </div>
                                            @endif
                                            <div class="text-xs text-zinc-400">
                                                {{ $resource->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($resource->resourceable->price)
                                        <flux:badge color="emerald">
                                            {{ $resource->resourceable->price }} {{ $resource->resourceable->currency }}
                                        </flux:badge>
                                    @else
                                        &mdash;
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex flex-col gap-2">
                                        @if($resource->resourceable->tags && count($resource->resourceable->tags) > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($resource->resourceable->tags as $tag)
                                                    <flux:badge color="blue" size="xs">{{ $tag }}</flux:badge>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        @if($resource->resourceable->categories && count($resource->resourceable->categories) > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($resource->resourceable->categories as $category)
                                                    <flux:badge color="purple" size="xs">{{ $category }}</flux:badge>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        @if((!$resource->resourceable->tags || count($resource->resourceable->tags) === 0) && 
                                           (!$resource->resourceable->categories || count($resource->resourceable->categories) === 0))
                                            <span class="text-zinc-400 text-xs">No tags or categories</span>
                                        @endif
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($resource->words_count)
                                        <flux:badge>{{ $resource->words_count }}</flux:badge>
                                    @else
                                        &mdash;
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($resource->status)
                                        <flux:badge color="{{ $resource->status->color() }}" size="sm">
                                            <flux:icon icon="{{ $resource->status->icon() }}" variant="micro" class="mr-2"/>
                                            {{ $resource->status->label() }}
                                        </flux:badge>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6">
                                    <div class="flex items-center justify-center py-8 text-zinc-400">
                                        No products found
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>

            <div class="sticky bottom-0 w-full p-2 mt-2" x-show="visible" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform translate-y-2">
                <div class="bg-zinc-950 text-white py-3 px-4 rounded-2xl shadow-xl flex items-center gap-2 text-sm">
                    <span>You have <b x-text="selectedCount + ' product' + (selectedCount !== 1 ? 's' : '')"></b> selected</span>
                    <flux:spacer />

                    <flux:button wire:click="delete(selected)" icon="trash" variant="primary">Delete</flux:button>
                    <flux:button wire:click="hide(selected)" icon="eye-slash" variant="primary">Hide</flux:button>
                    <flux:button wire:click="process(selected)" icon="plus">Process</flux:button>
                </div>
            </div>
        </flux:checkbox.group>
    </flux:card>
</div> 