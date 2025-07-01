<?php

namespace App\Jobs;

use App\Models\ProcessedEmail;
use DirectoryTree\ImapEngine\Mailbox;
use DirectoryTree\ImapEngine\DraftMessage;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use OpenAI;
use Illuminate\Support\Facades\Config;

class GenerateEmailResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ProcessedEmail $processedEmail
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Skip if the email has already been replied to
        if ($this->processedEmail->was_replied) {
            Log::info("Email already replied to: {$this->processedEmail->id}");
            return;
        }

        try {
            // Generate AI response
            $response = $this->generateAIResponse();
            
            // Send the response via email
            $this->sendEmailResponse($response['content']);
            
            // Mark the email as replied
            $this->processedEmail->markAsReplied(
                $response['content'], 
                $response['total_tokens']
            );
            
            Log::info("Successfully replied to email: {$this->processedEmail->id}");
            
        } catch (Exception $e) {
            Log::error("Error generating response for email {$this->processedEmail->id}: {$e->getMessage()}");
        }
    }
    
    /**
     * Generate an AI response using Groq.
     */
    private function generateAIResponse(): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->getSystemPrompt()
            ],
            [
                'role' => 'user',
                'content' => $this->processedEmail->original_message
            ]
        ];

        $parameters = [
            'model' => 'meta-llama/llama-4-maverick-17b-128e-instruct',
            'temperature' => 0.7,
            'messages' => $messages,
            'max_completion_tokens' => 1000
        ];

        $client = OpenAI::factory()
            ->withApiKey(config('services.groq.api_key'))
            ->withBaseUri('api.groq.com/openai/v1')
            ->make();

        try {
            $result = $client->chat()->create($parameters);

            $content = $result->choices[0]->message->content;
            $totalTokens = $result->usage->totalTokens ?? 0;
            
            return [
                'content' => $content,
                'total_tokens' => $totalTokens,
            ];
        } catch (Exception $e) {
            Log::error('AI Response Error: ' . $e->getMessage());
            
            // Fallback response
            return [
                'content' => "Thank you for your email. I apologize, but I'm currently experiencing technical difficulties. A human team member will review your message shortly.",
                'total_tokens' => 0,
            ];
        }
    }
    
    /**
     * Send an email response to the original sender.
     */
    private function sendEmailResponse(string $responseContent): void
    {
        $inbox = $this->processedEmail->inbox;
        
        // Configure email to be sent from the authorized AWS SES address
        $config = [
            'driver' => 'smtp',
            'host' => $inbox->smtp_host,
            'port' => $inbox->smtp_port,
            'encryption' => null,
            'username' => $inbox->username,
            'password' => $inbox->password,
            'from' => [
                'address' => $inbox->username,
                'name' => $inbox->name,
            ],
        ];
        
        // Create a properly formatted HTML version
        $htmlContent = '<p>' . nl2br(htmlspecialchars($responseContent)) . '</p>';
        
        // Apply the mail configuration
        Config::set('mail', $config);
        
        // Clear any cached mail configurations
        if (app()->bound('mailer')) {
            app()->forgetInstance('mailer');
        }
        app('mail.manager')->setDefaultDriver($config['driver']);
        
        // Send the email using Laravel's mail facade with specific configuration
        try {
            Mail::send([], [], function ($message) use ($responseContent, $htmlContent, $inbox, $config) {
                $message->from($config['from']['address'], $config['from']['name'])
                        ->to($this->processedEmail->from_email)
                        ->subject('Re: ' . $this->processedEmail->subject)
                        ->text($responseContent)
                        ->html($htmlContent);
            });
            
            Log::info("Successfully sent email response to: {$this->processedEmail->from_email}");
        } catch (Exception $e) {
            Log::error("Failed to send email: " . $e->getMessage());
            
            // Try with an alternative approach for compatibility
            try {
                Mail::raw($responseContent, function ($message) use ($inbox, $config) {
                    $message->from($config['from']['address'], $config['from']['name'])
                            ->to($this->processedEmail->from_email)
                            ->subject('Re: ' . $this->processedEmail->subject);
                });
                
                Log::info("Successfully sent email (alternative method) to: {$this->processedEmail->from_email}");
            } catch (Exception $e) {
                Log::error("Failed to send email (alternative method): " . $e->getMessage());
                throw $e;
            }
        }
    }
    
    /**
     * Get the system prompt for the AI.
     */
    private function getSystemPrompt(): string
    {
        return <<<EOT
You are an AI assistant tasked with responding to customer emails. Your goal is to provide helpful, clear, and concise responses.

### Email Context:
- Responding to: {$this->processedEmail->from_name} <{$this->processedEmail->from_email}>
- Subject: {$this->processedEmail->subject}

### Guidelines:
- Be professional and courteous
- Address the specific questions or concerns in the email
- Keep responses concise but thorough
- Use a friendly, helpful tone
- Sign off as "AI Assistant" at the end of your message
- Format your response as a proper email with greeting and closing

### Response Structure:
1. Greeting (e.g., "Hello [Name]," or "Dear [Name],")
2. Brief acknowledgment of their email
3. Main response addressing their questions or concerns
4. Closing statement
5. Sign-off (e.g., "Best regards, AI Assistant")

Do not mention that you are an AI in the body of your email, only in the signature. Respond as if you're a helpful customer service representative.
EOT;
    }
} 