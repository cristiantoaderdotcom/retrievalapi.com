<?php

namespace App\Livewire\App\EmailInbox\Settings;

use App\Models\EmailInbox;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public EmailInbox $inbox;
    public $name;
    public $host;
    public $port;
    public $encryption;
    public $validate_cert;
    public $username;
    public $password;
    public $is_active;
    public $settings;
    
    protected $rules = [
        'name' => 'required|string|max:100',
        'host' => 'required|string|max:255',
        'port' => 'required|integer',
        'encryption' => 'required|in:ssl,tls,none',
        'validate_cert' => 'boolean',
        'username' => 'required|email|max:255',
        'password' => 'nullable|string',
        'is_active' => 'boolean',
        'settings' => 'nullable|array',
    ];
    
    public function mount(EmailInbox $id)
    {
        if ($id->user_id !== Auth::id()) {
            abort(403);
        }
        
        $this->inbox = $id;
        $this->fill($this->inbox->only([
            'name', 'host', 'port', 'encryption', 'validate_cert', 
            'username', 'is_active', 'settings'
        ]));
        
        // Don't populate the password field for security
        $this->password = '';
    }
    
    public function save()
    {
        $this->validate();
        
        $data = [
            'name' => $this->name,
            'host' => $this->host,
            'port' => $this->port,
            'encryption' => $this->encryption,
            'validate_cert' => $this->validate_cert,
            'username' => $this->username,
            'is_active' => $this->is_active,
            'settings' => $this->settings ?? [],
        ];
        
        // Only update the password if a new one was provided
        if (!empty($this->password)) {
            $data['password'] = $this->password;
        }
        
        $this->inbox->update($data);
        
        $this->dispatch('settings-saved');
    }
    
    public function toggleActive()
    {
        $this->is_active = !$this->is_active;
        $this->save();
    }
    
    public function deleteInbox()
    {
        $this->inbox->delete();
        return redirect()->route('app.email-inbox.index');
    }
    
    public function render()
    {
        return view('livewire.app.email-inbox.settings.index')
            ->extends('layouts.app')
            ->section('main');
    }
}