<?php

namespace App\Livewire\App\Workspace;

use App\Models\Language;
use Illuminate\Support\Collection;
use Livewire\Component;
use Illuminate\Http\Request;
use App\Models\Workspace;
use Illuminate\Support\Str;
use Flux\Flux;

class WorkspaceCreate extends Component
{
    public $name;
    public $language_id = 1;

    public Collection $languages;

    
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

    public array $platform_website = [
        'welcome_message' => 'Hello! How can I help you today?',
		'fallback_message' => 'I am sorry, I do not have the answer to that question. Please try asking me something else.',
		'suggested_messages' => 'Whats the prices?',
		'message_placeholder' => 'Type your message here...',
		'remove_iframe_branding' => false,
        'user_recognition' => true,
        'conversation_continuity' => true,
        'send_on_enter' => true,
        'reset_button' => true,
    ];

    public array $styling = [
        'theme' => 'default',
		'font_family' => 'system-ui',
		'font_size' => '16px',
        'custom_colors' => [
            'primary' => '#4f46e5',
            'secondary' => '#6b7280',
            'background' => '#ffffff',
            'text' => '#333333',
            'chat_bubble_user' => '#f1f5f9',
            'chat_bubble_assistant' => '#eff6ff',
            'chat_text_user' => '#1e293b',
            'chat_text_assistant' => '#1e293b',
        ],
    ];

    public function mount()
    {
        $this->languages = Language::query()->get();
    }

    public function store(Request $request)
    {
        $this->validate([
            'name' => 'required|string',
			'language_id' => 'required|integer',
        ]);

        $workspace = Workspace::create([
            'uuid' => Str::uuid()->toString(),
			'user_id' => auth()->id(),
            'name' => $this->name,
            'language_id' => $this->language_id,
        ]);

        $workspace->settings()->updateOrCreate(
            ['key' => 'general'],
            ['value' => $this->general]
        );

        $workspace->settings()->updateOrCreate(
            ['key' => 'business'],
            ['value' => $this->business]
        );

        $workspace->settings()->updateOrCreate(
            ['key' => 'training'],
            ['value' => $this->training]
        );

        $workspace->settings()->updateOrCreate(
            ['key' => 'platform_website'],
            ['value' => $this->platform_website]
        );
        
        $workspace->settings()->updateOrCreate(
            ['key' => 'styling'],
            ['value' => $this->styling]
        );

        Flux::toast(variant: 'success', text: 'Workspace created successfully');

        $request->session()->put('workspace', $workspace->toArray());

        return redirect()->route('app.workspace.knowledge-base.import-content', ['uuid' => $workspace->uuid]);
    }

    public function render()
    {
        return view('livewire.app.workspace.workspace-create')
        ->extends('layouts.app')
        ->section('main');
    }
}
