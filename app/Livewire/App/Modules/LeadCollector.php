<?php

namespace App\Livewire\App\Modules;

use App\Models\Workspace;
use App\Models\WorkspaceSetting;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Flux\Flux;

class LeadCollector extends Component
{

    #[Locked]
    public Workspace $workspace;

    public array $lead_collector = [
		'lead-enabled' => false,
		'lead-mandatory_form_submission' => true,
		'lead-trigger_after_messages' => 2,
		'lead-heading_message' => 'To provide you with better assistance, could you please share your contact details?',
		'lead-button_label' => 'Continue chatting...',
		'lead-confirmation_message' => 'Thanks for sharing your contact details! How else can I help you?',
	];

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->with('settings')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $this->lead_collector = $this->workspace->setting('lead_collector', $this->lead_collector);
    }

    public function render()
    {
        return view('livewire.app.modules.lead-collector')
            ->extends('layouts.app')
            ->section('main');
    }

    public function storeLeadCollector() {
		$this->validate([
			'lead_collector.lead-enabled' => 'required|boolean',
			'lead_collector.lead-mandatory_form_submission' => 'required|boolean',
			'lead_collector.lead-trigger_after_messages' => 'required|integer|min:1',
			'lead_collector.lead-heading_message' => 'required|string|max:255',
			'lead_collector.lead-button_label' => 'required|string|max:255',
			'lead_collector.lead-confirmation_message' => 'required|string|max:255',
		]);

		$this->workspace->settings()->updateOrCreate(
			['key' => 'lead_collector'],
			['value' => $this->lead_collector]
		);

		Flux::toast(variant: 'success', text: 'Lead Collector updated successfully');
	}
}
