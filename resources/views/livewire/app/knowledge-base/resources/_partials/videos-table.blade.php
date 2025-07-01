<div x-data="{
		selected: $wire.entangle('selected'),
		get selectedCount() {
			return Object.values(this.selected).filter(value => value).length;
		},
		get visible() {
			return this.selectedCount > 0;
		},
	}">
	<flux:card class="relative">
		@if($showTooltip)
			<img src="{{ asset('assets/images/process.webp') }}" class="absolute top-27 -mr-4 right-full w-50 hidden lg:block pointer-events-none" />
		@endif
		
		<flux:checkbox.group class="!select-auto">
				<form wire:submit="filter">
					<div x-data="{ filtering: false }" class="rounded-t-lg p-3">
					<div class="flex items-center flex-wrap gap-3">
						<span class="text-xs">You have <b x-text="selectedCount + ' video' + (selectedCount !== 1 ? 's' : '')"></b> selected</span>
						<flux:separator vertical class="my-2" variant="subtle" />

						<flux:button size="sm" @click="visible && $wire.process(selected)"  icon="plus" variant="primary" ::class="{ '!cursor-not-allowed opacity-50': !visible }">Process</flux:button>

						<flux:spacer />
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
							<flux:select wire:model="filters.status"  variant="listbox" size="sm" class="max-w-fit">
								<flux:select.option value="" selected>All Statuses</flux:select.option>
								@foreach($this->statuses as $status)
									<flux:select.option value="{{ data_get($status, 'value') }}">
										<div class="flex items-center gap-2">
											@if($icon = data_get($status, 'icon'))
												<flux:icon :name="$icon" variant="micro" class="text-{{ data_get($status, 'color') }}-500"/>
											@endif
											{{ data_get($status, 'label') }}
										</div>
									</flux:select.option>
								@endforeach
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

			<div class="[&>div>div:nth-of-type(1)]:rounded-t-none [&_table]:bg-white dark:[&_table]:bg-gray-800">
				<flux:table :paginate="$resources">
					<flux:table.columns>
						<flux:table.column class="w-11">
							<flux:checkbox.all />
						</flux:table.column>
						<flux:table.column>Title</flux:table.column>
						<flux:table.column>Words</flux:table.column>
						<flux:table.column>Characters</flux:table.column>
						<flux:table.column>Status</flux:table.column>
						<flux:table.column></flux:table.column>
					</flux:table.columns>

					<flux:table.rows>
						@forelse($resources as $resource)
							<flux:table.row wire:key="row-{{ $resource->id }}">
								<flux:table.cell>
									<div class="flex items-center justify-center">
										@if($resource->process_started_at && $resource->status->isProcessing())
											<flux:icon.loading variant="mini"/>
										@else
											<flux:checkbox
												wire:model="selected.{{ $resource->id }}"
												@class([
													'opacity-20' => $resource->process_completed_at && !$resource->status->isFailed(),
												])
											/>
										@endif
									</div>
								</flux:table.cell>
								<flux:table.cell>
									<div class="flex flex-col w-80 whitespace-normal">
										<div class="break-all">
											{{ str($resource->resourceable->title)->limit(90) }}
										</div>
										<div class="text-xs text-zinc-400">
											{{ $resource->created_at->diffForHumans() }}
										</div>
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
									@if($resource->characters_count)
										<flux:badge>{{ $resource->characters_count }}</flux:badge>
									@else
										&mdash;
									@endif
								</flux:table.cell>
								<flux:table.cell>
									@if($resource->status)
										<flux:badge color="{{ $resource->status->color() }}" size="sm">
											<flux:icon :name="$resource->status->icon()" variant="micro" class="mr-2"/>
											{{ $resource->status->label() }}
										</flux:badge>
									@endif
								</flux:table.cell>
								<flux:table.cell class="flex justify-end">
									<flux:button size="sm" @click="$wire.delete({{ $resource->id }})"  icon="trash"/>
								</flux:table.cell>
							</flux:table.row>
						@empty
							<flux:table.row>
								<flux:table.cell colspan="5">
									<div class="flex items-center justify-center py-8 text-zinc-400">
										No resources found
									</div>
								</flux:table.cell>
							</flux:table.row>
						@endforelse
					</flux:table.rows>
				</flux:table>
			</div>
		</flux:checkbox.group>
	</flux:card>
</div>

