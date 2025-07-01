<div>
	<flux:card class="space-y-6">
		<form wire:submit="store" class="space-y-6">
			<flux:input wire:model="url" placeholder="https://www.youtube.com/watch?v=xxxxxx" label="URL"
				description="Enter the YouTube / X (Twitter), TikTok, Facebook, Dailymotion, Vimeo, Loom video URL you want to to train your AI model with." />

			<flux:button type="submit" variant="primary" icon-trailing="plus">Submit</flux:button>
		</form>

		@include('livewire.app.knowledge-base.resources._partials.stats')
		@include('livewire.app.knowledge-base.resources._partials.videos-table')
	</flux:card>
</div>
