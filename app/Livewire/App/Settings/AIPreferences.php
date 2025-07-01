<?php

namespace App\Livewire\App\Settings;

use App\Models\Workspace;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Locked;

class AIPreferences extends Component
{
    #[Locked]
    public Workspace $workspace;

    public string $tab = 'general';

    public array $general = [
        'instructions' => '',
		'fallback_response' => 'Can\'t answer that, please try to rephrase your question.',
		'temperature' => '0.5',
		'tone' => 'professional',
		'response_length' => 'concise',
		'message_style' => 'direct',
		'custom_rules' => '',
		'max_tokens' => 200,
		'conversation_memory' => 3,
		'knowledge_limitations' => true,
    ];

    public array $business = [
        'name' => '',
        'description' => '',
        'website' => '',
        'audience' => '',
    ];

    public array $training = [
        'instructions' => '',
		'rules' => '',
		'links' => true,
		'temperature' => '0.5',
		'length' => 'concise',
    ];

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->with('settings')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $this->general = $this->workspace->setting('general', $this->general);
        $this->business = $this->workspace->setting('business', $this->business);
        $this->training = $this->workspace->setting('training', $this->training);
    }

    public function saveGeneral()
    {
        $this->workspace->settings()->updateOrCreate(
            ['key' => 'general'],
            ['value' => $this->general]
        );

        Flux::toast(variant: 'success', text: 'General preferences updated successfully');
    }

    public function saveBusiness()
    {
        $this->workspace->settings()->updateOrCreate(
            ['key' => 'business'],
            ['value' => $this->business]
        );

        Flux::toast(variant: 'success', text: 'Business preferences updated successfully');
    }

    public function saveTraining()
    {
        $this->workspace->settings()->updateOrCreate(
            ['key' => 'training'],
            ['value' => $this->training]
        );

        Flux::toast(variant: 'success', text: 'Training preferences updated successfully');
    }

    public function render()
    {
        return view('livewire.app.settings.ai-preferences')
            ->extends('layouts.app')
            ->section('main');
    }
}
