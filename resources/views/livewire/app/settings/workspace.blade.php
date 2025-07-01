<div class="container mx-auto space-y-6">
	

	<div class="space-y-6">
		<flux:card>
			<div class="space-y-6">
				<div>
					<h3 class="text-lg font-medium ">General Settings</h3>
					<p class="mt-1 text-sm">Update your workspace's settings.</p>
				</div>

				<form class="space-y-6" wire:submit.prevent="store">
					<flux:input type="text" label="Workspace Name" wire:model="name" />

					<flux:select label="Primary Language" searchable variant="listbox" wire:model="language_id">
						@foreach ($languages as $language)
							<flux:select.option value="{{ $language->id }}">
								<div class="flex items-center gap-2">
									<img alt="{{ $language->name }}" class="max-w-5" src="{{ asset('assets/icons/languages/' . $language->code . '.svg') }}" />
									{{ $language->name }}
								</div>
							</flux:select.option>
						@endforeach
					</flux:select>

					<div>
						<flux:button icon="check" type="submit" variant="primary">
							Save Changes
						</flux:button>
					</div>
				</form>
			</div>
		</flux:card>

		<flux:card>
			<div class="space-y-6">
				<div>
					<h3 class="text-lg font-medium text-red-600">Danger Zone</h3>
					<p class="mt-1 text-sm text-gray-500">Irreversible and destructive actions.</p>
				</div>

				<div class="rounded-lg bg-red-50 p-4">
					<div class="flex">
						<div class="flex-shrink-0">
							<flux:icon class="h-5 w-5 text-red-400" name="exclamation-triangle" />
						</div>
						<div class="ml-3">
							<h3 class="text-sm font-medium text-red-800">Delete this workspace</h3>
							<div class="mt-2 text-sm text-red-700">
								<p>Once you delete a workspace, there is no going back. Please be certain.</p>
							</div>
							<div class="mt-4">
								<flux:modal.trigger name="delete-workspace">
									<flux:button variant="danger">Delete Workspace</flux:button>
								</flux:modal.trigger>
							</div>
						</div>
					</div>
				</div>
			</div>
		</flux:card>
	</div>

	<flux:modal class="md:w-4xl w-full" name="delete-workspace">
		<div class="space-y-6">
			<div>
				<h3 class="text-lg font-medium text-gray-900">Delete Workspace</h3>
				<p class="mt-1 text-sm text-gray-500">Are you sure you want to delete this workspace? This action cannot be undone.
				</p>
			</div>

			<div class="rounded-lg bg-red-50 p-4">
				<div class="flex">
					<div class="flex-shrink-0">
						<flux:icon class="size-5 text-red-400" name="exclamation-triangle" />
					</div>
					<div class="ml-3">
						<h3 class="text-sm font-medium text-red-800">Warning</h3>
						<div class="mt-2 text-sm text-red-700">
							<ul class="list-disc space-y-1 pl-5">
								<li>All workspace data will be permanently deleted</li>
								<li>Existing chat interfaces will stop working</li>
								<li>Training data and configurations will be lost</li>
							</ul>
						</div>
					</div>
				</div>
			</div>

			<div class="flex justify-end gap-3">
				<flux:button class="!bg-gray-100 !text-gray-700 hover:!bg-gray-200" variant="primary" x-on:click="$flux.modal('delete-workspace').close()">
					Cancel
				</flux:button>

				<flux:button variant="danger" wire:click="destroy">
					Delete Workspace
				</flux:button>
			</div>
		</div>
	</flux:modal>

</div>
