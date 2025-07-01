<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;
use App\Models\Workspace as WorkspaceModel;
use Livewire\Attributes\Locked;
use Illuminate\Support\Collection;
use App\Models\Language;
use Flux\Flux;
class Workspace extends Component
{
    #[Locked]
    public WorkspaceModel $workspace;

    public string $name;

    public int $language_id;

    public Collection $languages;

    protected $rules = [
        'name' => ['required', 'string', 'min:3', 'max:255'],
        'language_id' => ['required', 'exists:languages,id'],
    ];

    public function mount($uuid)
    {
        $this->workspace = WorkspaceModel::query()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        $this->languages = Language::query()->get();

        $this->name = $this->workspace->name;
        $this->language_id = $this->workspace->language_id;

    }
    public function store() {
        $validated = $this->validate();

        $this->workspace->update($validated);

        Flux::toast(variant: 'success', text: 'Workspace updated successfully');
    }

    public function destroy() {
        $this->workspace->delete();

        Flux::toast(variant: 'success', text: 'Workspace deleted successfully');

        sleep(1);

        session()->forget('workspace');

        return redirect()->route('app.index');
    }
    public function render()
    {
        return view('livewire.app.settings.workspace')
            ->extends('layouts.app')
            ->section('main');
    }
}
