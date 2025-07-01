<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEmailInbox;
use App\Models\EmailInbox;
use Illuminate\Console\Command;

class ProcessEmailInboxes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:process {--inbox=} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process emails from one or all inboxes and generate AI responses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inboxId = $this->option('inbox');
        $processAll = $this->option('all');
        
        if (!$inboxId && !$processAll) {
            $this->error('You must specify either an inbox ID with --inbox=ID or use --all to process all inboxes.');
            return 1;
        }
        
        if ($inboxId) {
            $inbox = EmailInbox::find($inboxId);
            
            if (!$inbox) {
                $this->error("Email inbox with ID {$inboxId} not found.");
                return 1;
            }
            
            $this->processInbox($inbox);
            return 0;
        }
        
        if ($processAll) {
            $inboxes = EmailInbox::where('is_active', true)->get();
            
            if ($inboxes->isEmpty()) {
                $this->info('No active email inboxes found.');
                return 0;
            }
            
            $this->info("Processing {$inboxes->count()} email inboxes...");
            
            foreach ($inboxes as $inbox) {
                $this->processInbox($inbox);
            }
            
            return 0;
        }
    }
    
    /**
     * Process a single email inbox.
     */
    private function processInbox(EmailInbox $inbox): void
    {
        $this->info("Processing inbox: {$inbox->name} ({$inbox->username})");
        
        // Dispatch the job to process this inbox
        ProcessEmailInbox::dispatch($inbox);
    }
} 