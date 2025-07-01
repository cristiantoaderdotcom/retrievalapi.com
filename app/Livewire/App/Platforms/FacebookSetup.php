<?php

namespace App\Livewire\App\Platforms;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\FacebookPage;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\Redirect;

class FacebookSetup extends Component
{
    #[Locked]
    public Workspace $workspace;
    
    #[Locked]
    public $facebookPage;
    
    public $page_name = '';
    public $page_id = '';
    public $page_access_token = '';
    public $handle_messages = true;
    public $handle_comments = true;
    
    public function mount(string $uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        // Check if workspace already has a Facebook page
        if ($existingPage = $this->workspace->facebookPage) {
            // If the page is complete, redirect to the main Facebook page
            if ($existingPage->page_name && $existingPage->page_access_token) {
                return redirect()->route('app.workspace.platforms.facebook', ['uuid' => $this->workspace->uuid]);
            }
            
            // Use existing page if it's incomplete
            $this->facebookPage = $existingPage;
            
            // Pre-populate form fields if there's any data
            if ($existingPage->page_name) $this->page_name = $existingPage->page_name;
            if ($existingPage->page_id) $this->page_id = $existingPage->page_id;
            if ($existingPage->page_access_token) $this->page_access_token = $existingPage->page_access_token;
            $this->handle_messages = $existingPage->handle_messages;
            $this->handle_comments = $existingPage->handle_comments;
        } else {
            // Create a new empty Facebook page record
            $this->facebookPage = FacebookPage::create([
                'user_id' => auth()->id(),
                'workspace_id' => $this->workspace->id,
                'uuid' => Str::uuid(),
                'page_verify_token' => Str::random(32),
                'is_active' => false,
            ]);
        }
    }
    
    public function render()
    {
        return view('livewire.app.platforms.facebook-setup')
            ->extends('layouts.app')
            ->section('main');
    }
    
    public function update()
    {
        $this->facebookPage->update([
            'page_name' => $this->page_name,
            'page_id' => $this->page_id,
            'page_access_token' => $this->page_access_token,
            'handle_messages' => $this->handle_messages,
            'handle_comments' => $this->handle_comments,
            'is_active' => true,
        ]);
        
        return redirect()->route('app.workspace.platforms.facebook', ['uuid' => $this->workspace->uuid]);
    }
}
