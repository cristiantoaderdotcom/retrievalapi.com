<?php

namespace App\Livewire\App\Platforms;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\DiscordBot;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Redirect;

class Discord extends Component
{
    #[Locked]
    public Workspace $workspace;
    
    public $discordBot = null;

    public function mount(string $uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        $this->loadDiscordBot();
        
        // Automatically redirect to setup if no Discord bot is configured
        if (!$this->discordBot) {
            return redirect()->route('app.workspace.platforms.discord.setup', ['uuid' => $this->workspace->uuid]);
        }
    }
    
    public function loadDiscordBot()
    {
        $this->discordBot = $this->workspace->discordBot;
    }
    
    public function setupDiscord()
    {
        return Redirect::route('app.workspace.platforms.discord.setup', ['uuid' => $this->workspace->uuid]);
    }

    public function render()
    {
        return view('livewire.app.platforms.discord')
            ->extends('layouts.app')
            ->section('main');
    }
}
