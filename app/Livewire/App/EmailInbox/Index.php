<?php

namespace App\Livewire\App\EmailInbox;

use App\Models\EmailInbox;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $inboxes = EmailInbox::where('user_id', Auth::id())->get();
        
        return view('livewire.app.email-inbox.index', [
            'inboxes' => $inboxes,
        ])
            ->extends('layouts.app')
            ->section('main');
    }
}