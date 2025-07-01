<?php

namespace App\Livewire\App\EmailInbox;

use App\Models\EmailInbox;
use App\Models\ProcessedEmail;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;
    
    #[Locked]
    public EmailInbox $inbox;
    public $selectedEmail = null;
    public $filter = 'all'; // all, replied, not_replied
    
    public function mount($uuid)
    {
        $this->inbox = EmailInbox::where('uuid', $uuid)->firstOrFail();
    }
    
    public function selectEmail($emailId)
    {
        $this->selectedEmail = ProcessedEmail::findOrFail($emailId);
    }
    
    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }
    
    public function render()
    {
        $query = $this->inbox->processedEmails();
        
        if ($this->filter === 'replied') {
            $query->where('was_replied', true);
        } elseif ($this->filter === 'not_replied') {
            $query->where('was_replied', false);
        }
        
        $emails = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('livewire.app.email-inbox.show', [
            'emails' => $emails,
        ])
            ->extends('layouts.app')
            ->section('main');
    }
}