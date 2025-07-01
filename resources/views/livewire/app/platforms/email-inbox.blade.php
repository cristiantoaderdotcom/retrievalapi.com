<div>
    @if(!$this->emailInbox)
        <livewire:app.platforms.email-inbox-setup :workspace="$workspace" />
    @endif
</div>
