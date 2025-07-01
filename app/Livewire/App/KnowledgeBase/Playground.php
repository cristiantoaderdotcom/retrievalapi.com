<?php

namespace App\Livewire\App\KnowledgeBase;

use Livewire\Component;
use App\Models\Workspace;
use Livewire\Attributes\Locked;
class Playground extends Component
{
    #[Locked]
    public Workspace $workspace;

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->where('user_id', auth()->user()->id)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.app.knowledge-base.playground')
            ->extends('layouts.app')
            ->section('main');
    }
}
