<?php

namespace App\Livewire\App\Platforms;

use Livewire\Component;

class FontPreview extends Component
{
    public string $fontFamily = 'system-ui';
    public array $systemFonts = ['system-ui'];
    
    public function mount($fontFamily)
    {
        $this->fontFamily = $fontFamily;
    }
    
    public function formatGoogleFontName($font)
    {
        return str_replace('-', '+', $font);
    }
    
    // This method ensures the component updates correctly when the font changes
    public function updated($name, $value)
    {
        if ($name === 'fontFamily') {
            $this->fontFamily = $value;
        }
    }
    
    public function render()
    {
        return view('livewire.app.platforms.font-preview');
    }
} 