<?php

namespace App\Livewire\App\Platforms;

use App\Models\Workspace;
use Livewire\Component;
use Livewire\Attributes\Locked;

class EmailInbox extends Component
{
    public Workspace $workspace;

    public function mount($uuid) {
        $this->workspace = Workspace::query()
            ->with('emailInbox')
            ->where('user_id', auth()->id())
            ->where('uuid', $uuid)
            ->firstOrFail();

        $this->emailInbox = $this->workspace->emailInbox;
    }
    
    public function render()
    {
        return view('livewire.app.platforms.email-inbox')
            ->extends('layouts.app')
            ->section('main');
    }
}
