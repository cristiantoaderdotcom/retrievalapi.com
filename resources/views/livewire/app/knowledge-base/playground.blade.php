<div>
    <flux:card class="space-y-6">
        <flux:heading size="xl">Live Playground</flux:heading>
        <p class="text-sm text-gray-500">Here you can test your knowledge base before it goes live.</p>

        <iframe
            src="{{ route('embed.website.index', $workspace->uuid) }}"
            width="100%"
            height="100%"
            frameborder="0"
            style="min-height: 900px; border: none; border-radius: 1rem;">
        </iframe>
       
          
    </flux:card>
</div>
