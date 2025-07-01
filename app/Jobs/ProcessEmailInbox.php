<?php

namespace App\Jobs;

use App\Models\EmailInbox;
use App\Models\ProcessedEmail;
use DirectoryTree\ImapEngine\Mailbox;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmailInbox implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public function __construct(public EmailInbox $emailInbox)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->emailInbox->is_active) {
            Log::info("Skipping inactive email inbox: {$this->emailInbox->name}");
            return;
        }

        try {


            $config = [
                'port' => $this->emailInbox->imap_port,
                'username' => $this->emailInbox->username,
                'password' => $this->emailInbox->password,
                'encryption' => null,
                'host' => $this->emailInbox->imap_host,
            ];

            $mailbox = new Mailbox($config);
            $inbox = $mailbox->inbox();
            
            // Get messages from the last 24 hours with headers and body
            $messages = $inbox->messages()
                ->since(now()->subDay())
                ->withHeaders()
                ->withBody()
                ->get();
                
            Log::info("Found {$messages->count()} recent messages in {$this->emailInbox->name}");
            
            foreach ($messages as $message) {
                $this->processMessage($message);
            }
            
        } catch (Exception $e) {
            Log::error("Error processing email inbox {$this->emailInbox->name}: {$e->getMessage()}");
        }
    }
    
    /**
     * Process a single email message.
     */
    private function processMessage($message): void
    {
        // Skip if we've already processed this message
        $messageId = $message->messageId();
        if (empty($messageId)) {
            Log::warning("Skipping message with no Message-ID");
            return;
        }
        
        // Check if we've already processed this message
        if (ProcessedEmail::where('email_inbox_id', $this->emailInbox->id)
            ->where('message_id', $messageId)
            ->exists()) {
            return;
        }
        
        // Get message details
        $fromAddress = $message->from();
        $fromEmail = $fromAddress?->email();
        $fromName = $fromAddress?->name();
        $subject = $message->subject() ?? 'No Subject';
        $textContent = $message->text() ?? '';
        
        if (empty($fromEmail) || empty($textContent)) {
            Log::warning("Skipping message with missing sender email or content");
            return;
        }
        
        // Store the message in the database
        $processedEmail = ProcessedEmail::create([
            'email_inbox_id' => $this->emailInbox->id,
            'message_id' => $messageId,
            'subject' => $subject,
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'original_message' => $textContent,
        ]);
        
        // Dispatch job to generate AI response and reply
        GenerateEmailResponse::dispatch($processedEmail);
    }
} 