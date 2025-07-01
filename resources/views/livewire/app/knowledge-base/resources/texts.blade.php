<div>
	<flux:card class="space-y-6">
		<form wire:submit="store" class="space-y-6">
			<flux:field x-data="{ length: $wire.content?.length || 0, maxLength: 10000 }">
				<flux:label class="flex items-center justify-between">
					Texts
					
					<flux:badge icon="document-text" size="sm">
						<div x-text="maxLength - length"></div> &nbsp; characters remaining
					</flux:badge>
				</flux:label>
				<flux:description>Train the bot with specific texts that cannot be found on any public area of your website or docs.</flux:description>

				<flux:textarea wire:model="content" 
					rows="8" 
					maxlength="10000"
					x-on:input="length = $event.target.value.length" />
			</flux:field>

			<flux:button type="submit" variant="primary" icon-trailing="plus">Submit</flux:button>
		</form>

		@include('livewire.app.knowledge-base.resources._partials.stats')
		@include('livewire.app.knowledge-base.resources._partials.texts-table')
	</flux:card>
</div>
