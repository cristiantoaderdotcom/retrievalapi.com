<?php

namespace App\Livewire\App\Platforms;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\DiscordBot;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Flux\Flux;

class DiscordSetup extends Component
{
    #[Locked]
    public Workspace $workspace;
    
    #[Locked]
    public DiscordBot $discordBot;
    
    #[Rule('required|string')]
    public $guild_id = '';
    
    #[Rule('required|string')]
    public $command_prefix = 'ask';

    public function mount(string $uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        // Get existing bot or create a new one
        $this->discordBot = $this->workspace->discordBot ?? 
            DiscordBot::create([
                'user_id' => auth()->id(),
                'workspace_id' => $this->workspace->id,
                'uuid' => (string) Str::uuid(),
                'command_prefix' => 'ask',
                'is_active' => false,
            ]);
        
        if ($this->discordBot->guild_id) {
            $this->guild_id = $this->discordBot->guild_id;
        }
        
        if ($this->discordBot->command_prefix) {
            $this->command_prefix = $this->discordBot->command_prefix;
        }
    }

    public function render()
    {
        return view('livewire.app.platforms.discord-setup')
            ->extends('layouts.app')
            ->section('main');
    }
    
    public function update()
    {
        // Validate the inputs
        $this->validate([
            'guild_id' => 'required|string',
            'command_prefix' => 'required|string',
        ]);
        
        // Make sure command_prefix does not start with a slash
        $this->command_prefix = ltrim($this->command_prefix, '/');
        
        // Update the Discord bot in the database
        $this->discordBot->update([
            'guild_id' => $this->guild_id,
            'command_prefix' => $this->command_prefix,
            'bot_username' => 'ReplyElf', // Default display name
            'is_active' => true,
        ]);
        
        // Register slash command with Discord
        $response = $this->registerDiscordSlashCommand();
        
        // Store command registration status for potential use in UI
        if ($response['success']) {
            Log::channel('discord')->info('Slash command registration successful', [
                'bot_username' => 'ReplyElf',
                'guild_id' => $this->guild_id,
                'response' => $response['data']
            ]);
        } else {
            Log::channel('discord')->error('Slash command registration failed', [
                'bot_username' => 'ReplyElf',
                'guild_id' => $this->guild_id,
                'error' => $response['error']
            ]);
        }

        Flux::toast('Discord bot setup successful');

        return redirect()->route('app.workspace.platforms.discord', ['uuid' => $this->workspace->uuid]);
    }
    
    /**
     * Register slash command with Discord
     * 
     * @return array Response with success status and data/error message
     */
    private function registerDiscordSlashCommand(): array
    {
        try {
            $applicationId = config('services.discord.application_id');
            $botToken = config('services.discord.token');
            
            if (empty($applicationId) || empty($botToken)) {
                return [
                    'success' => false,
                    'error' => 'Discord bot credentials not configured in environment'
                ];
            }

            // Register command for specific guild
            $url = "https://discord.com/api/v10/applications/{$applicationId}/guilds/{$this->guild_id}/commands";
            
            // Define the command structure
            $commandData = [
                'name' => $this->command_prefix,
                'description' => 'Ask a question to get AI-powered assistance',
                'type' => 1, // CHAT_INPUT type
                'options' => [
                    [
                        'name' => 'question',
                        'description' => 'Your question',
                        'type' => 3, // STRING type
                        'required' => true
                    ]
                ]
            ];
            
            $response = Http::withHeaders([
                'Authorization' => "Bot {$botToken}",
                'Content-Type' => 'application/json'
            ])->post($url, $commandData);
            
            if ($response->successful()) {
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
