<div>
    <flux:card>
			<form wire:submit="storeLeadCollector" class="space-y-6">
				<flux:switch wire:model="lead_collector.lead-enabled" label="Enable Lead Collection"
					description="Activate this option to collect leads through the chatbot interface." />

				<flux:switch wire:model="lead_collector.lead-mandatory_form_submission" label="Mandatory Form Submission"
					description="If enabled, users must complete the form before continuing the chat." />

				<flux:input type="number" wire:model="lead_collector.lead-trigger_after_messages" label="Trigger After [X] Messages"
					description="Set the number of messages after which the lead collection form appears." />

				<flux:input wire:model="lead_collector.lead-heading_message" label="Lead Collection Prompt"
					description="Customize the message shown when asking users for their contact details." />

				<flux:input wire:model="lead_collector.lead-button_label" label="Button Label"
					description="Set the text displayed on the submission button." />

				<flux:input wire:model="lead_collector.lead-confirmation_message" label="Confirmation Message"
					description="Customize the message shown after the form is submitted." />

				<flux:button type="submit" icon="check" variant="primary">Save Changes</flux:button>
			</form>
		</flux:card>
</div>
