<?php

namespace App\Console\Commands;

use App\Models\ApiToken;
use App\Models\Workspace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:token:generate {workspace? : UUID of the workspace} {--name= : Name of the token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API token for a workspace';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $workspaceUuid = $this->argument('workspace');
        
        if (!$workspaceUuid) {
            // List workspaces for selection
            $workspaces = Workspace::all(['id', 'uuid', 'name']);
            
            if ($workspaces->isEmpty()) {
                $this->error('No workspaces found.');
                return Command::FAILURE;
            }
            
            $workspaceChoices = $workspaces->pluck('name', 'uuid')->toArray();
            $workspaceUuid = $this->choice('Select a workspace:', $workspaceChoices);
        }
        
        // Find the workspace
        $workspace = Workspace::where('uuid', $workspaceUuid)->first();
        
        if (!$workspace) {
            $this->error("Workspace with UUID $workspaceUuid not found.");
            return Command::FAILURE;
        }
        
        // Get token name
        $tokenName = $this->option('name');
        
        if (!$tokenName) {
            $tokenName = $this->ask('Enter a name for the token (e.g., "CLI Access"):');
        }
        
        // Validate the token name
        $validator = Validator::make(['name' => $tokenName], [
            'name' => 'required|string|min:3|max:255',
        ]);
        
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }
        
        // Generate the token
        $token = ApiToken::create([
            'workspace_id' => $workspace->id,
            'name' => $tokenName,
            'token' => ApiToken::generateToken(),
        ]);
        
        $this->info('API token generated successfully!');
        $this->newLine();
        $this->info('Workspace: ' . $workspace->name);
        $this->info('Token Name: ' . $token->name);
        $this->newLine();
        $this->line('Your API token (copy this now, it won\'t be shown again):');
        $this->newLine();
        $this->line($token->token);
        $this->newLine();
        $this->comment('Store this token securely as it will not be displayed again.');
        
        return Command::SUCCESS;
    }
} 