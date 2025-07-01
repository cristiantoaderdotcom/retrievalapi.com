<?php

namespace App\Livewire\App\Platforms;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\FacebookPage;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Redirect;

class Facebook extends Component
{
    #[Locked]
    public Workspace $workspace;
    
    public $facebookPage = null;

    public function mount(string $uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        $this->loadFacebookPage();
    }
    
    public function loadFacebookPage()
    {
        $this->facebookPage = $this->workspace->facebookPage;
    }
    
    public function setupFacebook()
    {
        return Redirect::route('app.workspace.platforms.facebook.setup', ['uuid' => $this->workspace->uuid]);
    }

    public function render()
    {
        return view('livewire.app.platforms.facebook')
            ->extends('layouts.app')
            ->section('main');
    }
}
