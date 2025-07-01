<?php

namespace App\Livewire\App\EmailInbox;

use App\Models\EmailInbox;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Create extends Component
{
    public $name = '';
    public $host = '';
    public $port = 993;
    public $encryption = 'ssl';
    public $validate_cert = true;
    public $username = '';
    public $password = '';
    public $smtp_host = '';
    public $smtp_port = 587;
    
    protected $rules = [
        'name' => 'required|string|max:100',
        'host' => 'required|string|max:255',
        'port' => 'required|integer',
        'encryption' => 'required|in:ssl,tls,none',
        'validate_cert' => 'boolean',
        'username' => 'required|email|max:255',
        'password' => 'required|string',
    ];
    
    public function create()
    {
        $this->validate();
        
        EmailInbox::create([
            'user_id' => Auth::id(),
            'uuid' => Str::uuid(),
            'name' => $this->name,
            'host' => $this->host,
            'port' => $this->port,
            'encryption' => $this->encryption,
            'validate_cert' => $this->validate_cert,
            'username' => $this->username,
            'password' => $this->password,
            'is_active' => true,
            'smtp_host' => $this->smtp_host ?? null,
            'smtp_port' => $this->smtp_port ?? null,
        ]);
        
        $this->reset();
        $this->dispatch('flux-modal-close', 'create-inbox');
        $this->dispatch('inbox-created');
        
        return redirect()->route('app.email-inbox.index');
    }
    
    public function render()
    {
        return view('livewire.app.email-inbox.create')
            ->extends('layouts.app')
            ->section('main');
    }
}