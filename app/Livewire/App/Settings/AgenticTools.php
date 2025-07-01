<?php

namespace App\Livewire\App\Settings;

use App\Models\Workspace;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Locked;

class AgenticTools extends Component
{
    #[Locked]
    public Workspace $workspace;

    public array $agentic_tools = [
        'refund_request' => [
            'enabled' => true,
            'schema' => [
                'email' => [
                    'type' => 'email',
                    'required' => true,
                    'label' => 'Email Address',
                    'validation' => 'email'
                ],
                'sale_id' => [
                    'type' => 'text',
                    'required' => true,
                    'label' => 'Sale ID',
                    'validation' => 'regex:/^[a-zA-Z]{3}-\d{4}$/',
                    'placeholder' => 'abc-1234'
                ]
            ]
        ],
        'bug_report' => [
            'enabled' => true,
            'schema' => [
                'description' => [
                    'type' => 'textarea',
                    'required' => true,
                    'label' => 'Bug Description',
                    'validation' => 'string|min:10'
                ],
                'steps_to_reproduce' => [
                    'type' => 'textarea',
                    'required' => false,
                    'label' => 'Steps to Reproduce',
                    'validation' => 'string'
                ]
            ]
        ]
    ];

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->with('settings')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        $this->agentic_tools = $this->workspace->setting('agentic_tools', $this->agentic_tools);
    }

    public function save()
    {
        $this->workspace->settings()->updateOrCreate(
            ['key' => 'agentic_tools'],
            ['value' => $this->agentic_tools]
        );

        Flux::toast(variant: 'success', text: 'Agentic tools settings updated successfully');
    }

    public function addField($toolName)
    {
        $this->agentic_tools[$toolName]['schema']['new_field_' . time()] = [
            'type' => 'text',
            'required' => false,
            'label' => 'New Field',
            'validation' => 'string'
        ];
    }

    public function removeField($toolName, $fieldName)
    {
        unset($this->agentic_tools[$toolName]['schema'][$fieldName]);
    }

    public function toggleTool($toolName)
    {
        $this->agentic_tools[$toolName]['enabled'] = !$this->agentic_tools[$toolName]['enabled'];
    }

    public function render()
    {
        return view('livewire.app.settings.agentic-tools')
            ->extends('layouts.app')
            ->section('main');
    }
}
