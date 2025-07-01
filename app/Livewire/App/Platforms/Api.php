<?php

namespace App\Livewire\App\Platforms;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\ApiToken;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Log;

class Api extends Component
{
    #[Locked]
    public Workspace $workspace;
    
    #[Rule('required|string|min:3|max:64')]
    public $tokenName = '';
    
    public $tokens = [];
    
    public function mount(string $uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        $this->loadTokens();
    }
    
    public function loadTokens()
    {
        $this->tokens = ApiToken::where('workspace_id', $this->workspace->id)
            ->orderByDesc('created_at')
            ->get();
    }
    
    public function createToken()
    {
        $this->validate();
        
        ApiToken::createForWorkspace($this->workspace, $this->tokenName);
        
        $this->tokenName = '';
        $this->loadTokens();
        
        \Flux\Flux::toast('API token created successfully');
    }
    
    public function deleteToken($id)
    {
        $token = ApiToken::where('id', $id)
            ->where('workspace_id', $this->workspace->id)
            ->first();
            
        if ($token) {
            $token->delete();
            $this->loadTokens();
            
            \Flux\Flux::toast('API token deleted successfully');
        }
    }
    
    public function render()
    {
        return view('livewire.app.platforms.api')
            ->extends('layouts.app')
            ->section('main');
    }
}
