<?php

namespace App\Livewire\App\Workspace;

use Livewire\Component;
use App\Models\Workspace;
use Livewire\Attributes\Locked;
class WorkspaceShow extends Component
{
    #[Locked]
    public Workspace $workspace;

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.app.workspace.workspace-show')
        ->extends('layouts.app')
        ->section('main');
    }
}
