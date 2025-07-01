<?php

namespace App\Livewire\App\Platforms;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\TelegramBot;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Redirect;

class Telegram extends Component
{
    #[Locked]
    public Workspace $workspace;
    
    public $telegramBot = null;

    public function mount(string $uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        $this->loadTelegramBot();
    }
    
    public function loadTelegramBot()
    {
        $this->telegramBot = $this->workspace->telegramBot;
    }
    
    public function setupTelegram()
    {
        return Redirect::route('app.workspace.platforms.telegram.setup', ['uuid' => $this->workspace->uuid]);
    }

    public function render()
    {
        return view('livewire.app.platforms.telegram')
            ->extends('layouts.app')
            ->section('main');
    }
}
