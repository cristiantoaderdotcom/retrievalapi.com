<?php

namespace App\Livewire\App\Platforms;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\TelegramBot;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramSetup extends Component
{
    #[Locked]
    public Workspace $workspace;
    
    #[Locked]
    public $telegramBot;
    
    public $bot_username = '';
    public $bot_token = '';
    public $command_prefix = '/ask';
    public $webhook_setup_status = null;
    
    public function mount(string $uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        // Check if workspace already has a Telegram bot
        if ($existingBot = $this->workspace->telegramBot) {
            // If the bot is complete, redirect to the main Telegram page
            if ($existingBot->bot_username && $existingBot->bot_token) {
                return redirect()->route('app.workspace.platforms.telegram', ['uuid' => $this->workspace->uuid]);
            }
            
            // Use existing bot if it's incomplete
            $this->telegramBot = $existingBot;
            
            // Pre-populate form fields if there's any data
            if ($existingBot->bot_username) $this->bot_username = $existingBot->bot_username;
            if ($existingBot->bot_token) $this->bot_token = $existingBot->bot_token;
            if ($existingBot->command_prefix) $this->command_prefix = $existingBot->command_prefix;
        } else {
            // Create a new empty Telegram bot record
            $this->telegramBot = TelegramBot::create([
                'user_id' => auth()->id(),
                'workspace_id' => $this->workspace->id,
                'uuid' => Str::uuid(),
                'is_active' => false,
            ]);
        }
    }
    
    public function render()
    {
        return view('livewire.app.platforms.telegram-setup')
            ->extends('layouts.app')
            ->section('main');
    }
    
    public function update()
    {
        // Validate the inputs
        $this->validate([
            'bot_username' => 'required|string',
            'bot_token' => 'required|string',
            'command_prefix' => 'required|string',
        ]);
        
        // Update the Telegram bot in the database
        $this->telegramBot->update([
            'bot_username' => $this->bot_username,
            'bot_token' => $this->bot_token,
            'command_prefix' => $this->command_prefix,
            'is_active' => true,
        ]);
        
        // Set up the webhook with Telegram API
        $webhookUrl = route('api.telegram.webhook', ['bot_uuid' => $this->telegramBot->uuid]);
        $response = $this->setTelegramWebhook($webhookUrl);
        
        // Store webhook setup status for potential use in UI
        if ($response['success']) {
            Log::channel('telegram')->info('Webhook setup successful', [
                'bot_username' => $this->bot_username,
                'response' => $response['data']
            ]);
        } else {
            Log::channel('telegram')->error('Webhook setup failed', [
                'bot_username' => $this->bot_username,
                'error' => $response['error']
            ]);
        }
        
        return redirect()->route('app.workspace.platforms.telegram', ['uuid' => $this->workspace->uuid]);
    }
    
    /**
     * Set the webhook URL for the Telegram bot
     * 
     * @param string $webhookUrl The webhook URL to set
     * @return array Response with success status and data/error message
     */
    private function setTelegramWebhook(string $webhookUrl): array
    {
        try {
            $url = "https://api.telegram.org/bot{$this->bot_token}/setWebhook";
            
            $response = Http::get($url, [
                'url' => $webhookUrl,
                'drop_pending_updates' => true
            ]);
            
            if ($response->successful() && $response->json('ok') === true) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }
            
            return [
                'success' => false,
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
