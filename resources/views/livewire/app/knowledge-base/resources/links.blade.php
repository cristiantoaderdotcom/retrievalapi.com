<div>
	<flux:card class="space-y-6">
		<form wire:submit="store" class="space-y-6">
			<flux:input wire:model="link" placeholder="https://" x-mask:dynamic="$addHttps($input)" label="Links"
				description="Enter the website URL you want to crawl and train your AI model with. You can also add a XML sitemap URL or a single page URL." />

			<div class="bg-gray-50 p-4 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
				<div class="flex items-center space-x-4">
					<div class="flex-shrink-0">
						<flux:switch wire:model="lookup" size="lg" />
					</div>
					<div class="flex-1">
						<div class="flex items-center">
							<h3 class="font-medium ">Auto-Discover Links</h3>
						</div>
						<p class="text-sm  mt-1">When turned ON, we will automatically find and include all links from the website you entered above!</p>
						<div class="mt-2 flex items-start space-x-2 text-xs ">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
								<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
							</svg>
							<span>Example: If you enter "yourwebsite.com", we'll automatically find pages like "yourwebsite.com/about", "yourwebsite.com/contact", etc.</span>
						</div>
					</div>
				</div>
			</div>

			<flux:button type="submit" variant="primary" icon-trailing="plus">Submit</flux:button>
		</form>

		@include('livewire.app.knowledge-base.resources._partials.stats')
		@include('livewire.app.knowledge-base.resources._partials.links-table')
	</flux:card>
</div>
