<?php

namespace App\Livewire\App\Platforms;

use App\Models\Workspace;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Models\EmailInbox;
use Illuminate\Support\Str;
class EmailInboxSetup extends Component
    {
        
    #[Locked]
    public Workspace $workspace;

    public string $name = '';
    public string $username = '';
    public string $password = '';
    public string $imap_host = '';
    public int $imap_port = 993;
    public string $smtp_host = '';
    public int $smtp_port = 587;
    public string $smtp_encryption = 'tls';
    public bool $validate_cert = true;

    public function mount($workspace)
    {
        $this->workspace = $workspace;
    }

    public function render()
    {
        return view('livewire.app.platforms.email-inbox-setup');
    }

    public function create()
    {
       

        $emailInbox = EmailInbox::create([
            'user_id' => auth()->user()->id,
            'workspace_id' => $this->workspace->id,
            'uuid' => Str::uuid(),
            'name' => $this->name,
            'username' => $this->username,
            'password' => $this->password,
            'imap_host' => $this->imap_host,
            'imap_port' => $this->imap_port,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_encryption' => $this->smtp_encryption,
            'validate_cert' => $this->validate_cert,
        ]);

        $this->dispatch('emailInboxCreated', emailInbox: $emailInbox);
    }
}
