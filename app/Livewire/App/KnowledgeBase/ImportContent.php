<?php

namespace App\Livewire\App\KnowledgeBase;

use App\Models\Workspace;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Http\Request;
class ImportContent extends Component
{
    public string $tab = 'links';

	public bool $locked = true;

	#[Locked]
	public Workspace $workspace;

	public function mount($uuid) {
		$this->workspace = Workspace::query()
			->where('uuid', $uuid)
			->firstOrFail();
	}

	public function changeTab($tab): void {
		$this->tab = $tab;
	}

    public function render(Request $request)
    {
        return view('livewire.app.knowledge-base.import-content')
            ->extends('layouts.app')
            ->section('main');
    }
}
