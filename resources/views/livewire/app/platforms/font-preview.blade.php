<div x-data="{
    fontFamily: @entangle('fontFamily').live,
    loadGoogleFont(font) {
        if (this.isSystemFont(font)) return;
        
        // Remove existing font link if any
        const existingLink = document.querySelector(`link[data-font-preview='${font}']`);
        if (existingLink) return; // Font already loaded
        
        // Create a new link element
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `https://fonts.googleapis.com/css2?family=${this.formatGoogleFontName(font)}:wght@400;700&display=swap`;
        link.setAttribute('data-font-preview', font);
        document.head.appendChild(link);
    },
    formatGoogleFontName(font) {
        return font.replace(/-/g, '+');
    },
    isSystemFont(font) {
        return ['system-ui'].includes(font);
    }
}" x-init="loadGoogleFont(fontFamily)" x-effect="loadGoogleFont(fontFamily)">
    <div class="p-3 border border-zinc-200 rounded-lg">
        <flux:badge size="sm" icon="language">Font Preview</flux:badge>
        <div :style="!isSystemFont(fontFamily) ? 
            `font-family: '${fontFamily}', sans-serif` : 
            `font-family: ${fontFamily}`" 
            class="text-lg">
            The font family used throughout the chat interface.
        </div>
    </div>
</div> 