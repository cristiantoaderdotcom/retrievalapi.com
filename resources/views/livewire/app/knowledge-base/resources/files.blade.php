<div>
	<flux:card class="space-y-6">
		<form wire:submit="store" class="space-y-6">
			<flux:field>
				<flux:label>Files</flux:label>
				<flux:description>
					Upload files that are relevant to this training step.
				</flux:description>

				<flux:input id="attachments-{{ rand() }}" type="file" wire:model="attachments" label="Attachments"
					description-trailing="Supported file types: pdf, doc, txt, csv, json, html"
					multiple accept=".pdf, .doc, .txt, .csv, .json, .html" />

				@error('attachments.*')
					<flux:error message="{{ $message }}" />
				@enderror

				<div wire:loading.flex wire:target="attachments" class="mt-4 items-center gap-3 text-sm">
					<flux:icon.loading variant="micro" />
					Uploading...
				</div>
			</flux:field>
		</form>

		@include('livewire.app.knowledge-base.resources._partials.stats')
		@include('livewire.app.knowledge-base.resources._partials.files-table')
	</flux:card>
</div>
