<?php

namespace App\Livewire\App\Platforms;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\InstagramPage;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Redirect;

class Instagram extends Component
{
    #[Locked]
    public Workspace $workspace;
    
    public $instagramPage = null;

    public function mount(string $uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        $this->loadInstagramPage();
    }
    
    public function loadInstagramPage()
    {
        $this->instagramPage = $this->workspace->instagramPage;
    }
    
    public function setupInstagram()
    {
        return Redirect::route('app.workspace.platforms.instagram.setup', ['uuid' => $this->workspace->uuid]);
    }

    public function render()
    {
        return view('livewire.app.platforms.instagram')
            ->extends('layouts.app')
            ->section('main');
    }
}
