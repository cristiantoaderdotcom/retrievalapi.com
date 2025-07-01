<?php

namespace App\Jobs\Reply;

use App\Enums\ConversationRole;
use App\Models\Workspace;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\InstagramPage;
use App\Services\Rag\RagService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LucianoTonet\GroqPHP\Groq;
use LucianoTonet\GroqPHP\GroqException;
use OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Instagram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Conversation $conversation;
    public ConversationMessage $message;
    private Workspace $workspace;
    private InstagramPage $page;
    private string $type;
    private RagService $ragService;

    public array $general;
    public array $business;
    public array $styling;
    public array $platform_instagram;

    /**
     * Create a new job instance.
     */
    public function __construct($conversation, $message, $page, $type = 'message', RagService $ragService = null) {
        $this->conversation = $conversation;
        $this->message = $message;
        $this->page = $page;
        $this->type = $type;
        $this->ragService = $ragService ?? app(RagService::class);
    }

    /**
     * Execute the job.
     */
    public function handle() {
        // Initialize workspace settings here to avoid serialization issues
        $this->workspace = Workspace::query()
            ->with('user', 'language', 'knowledgeBases', 'settings')
            ->where('id', $this->conversation->workspace_id)
            ->first();

        $this->general = $this->workspace->setting('general');
        $this->business = $this->workspace->setting('business');
        $this->styling = $this->workspace->setting('styling');
        $this->platform_instagram = $this->workspace->setting('platform_instagram') ?? [];
        
        // Check if user has reached their message limit
        $user = $this->workspace->user;
        if ($user && $user->messages_limit <= 0) {
            // Create message to inform about the limit
            $upgradeMessage = "I'm sorry, but the owner of this workspace has reached their message limit. Please contact them to upgrade their ReplyElf plan to continue using the AI assistant.";
            
            $message = ConversationMessage::query()
                ->create([
                    'conversation_id' => $this->conversation->id,
                    'role' => ConversationRole::ASSISTANT,
                    'message' => $upgradeMessage,
                    'total_tokens' => 0,
                ]);
                
            return $message;
        }
        
        $response = $this->response();

        $message = ConversationMessage::query()
            ->create([
                'conversation_id' => $this->conversation->id,
                'role' => ConversationRole::ASSISTANT,
                'message' => $response['content'],
                'total_tokens' => $response['total_tokens'],
            ]);

        // Decrease message limit for the user
        if ($user && $user->messages_limit > 0) {
            $user->decrement('messages_limit');
        }

        // Send response based on type
        if ($this->type === 'comment') {
            $this->replyToComment($response['content']);
        } else {
            $this->sendMessageToInstagram($response['content']);
        }

        return $message;
    }

    /**
     * Send message to Instagram
     */
    private function sendMessageToInstagram(string $content) {
        // Instagram uses a different endpoint than Facebook
        $url = "https://graph.facebook.com/v22.0/{$this->page->page_id}/messages";
        
        $recipientId = $this->conversation->type_source; // Should store sender PSID
        
        Log::channel('instagram')->info('Attempting to send message to Instagram', [
            'recipient_id' => $recipientId,
            'page_id' => $this->page->page_id,
            'content_length' => strlen($content),
            'access_token_length' => strlen($this->page->page_access_token),
            'conversation_source' => $this->conversation->source
        ]);
        
        try {
            $response = Http::withToken($this->page->page_access_token)
                ->post($url, [
                    'recipient' => [
                        'id' => $recipientId
                    ],
                    'message' => [
                        'text' => $content
                    ]
                ]);

            if (!$response->successful()) {
                // Log detailed error information
                $error = $response->json();
                Log::channel('instagram')->error('Failed to send message to Instagram', [
                    'error' => $response->body(),
                    'status' => $response->status(),
                    'recipient_id' => $recipientId,
                    'url' => $url,
                    'error_type' => $error['error']['type'] ?? 'unknown',
                    'error_code' => $error['error']['code'] ?? 0,
                    'error_message' => $error['error']['message'] ?? 'No specific error message'
                ]);
                
                // Special handling for permission errors
                if (isset($error['error']['code']) && $error['error']['code'] == 230) {
                    Log::channel('instagram')->error('PERMISSION ERROR: Instagram messaging requires pages_messaging permission', [
                        'solution' => 'Go to developers.facebook.com → App Review → Permissions and Features → Add pages_messaging permission'
                    ]);
                }
            } else {
                Log::channel('instagram')->info('Successfully sent message to Instagram', [
                    'recipient_id' => $recipientId,
                    'response' => $response->json()
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('instagram')->error('Exception sending message to Instagram', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Reply to an Instagram comment
     */
    private function replyToComment(string $content) {
        // Get the comment ID from the message metadata
        $commentId = $this->message->metadata['instagram']['comment_id'] ?? null;
        
        if (!$commentId) {
            Log::channel('instagram')->error('Failed to reply to comment - no comment ID found', [
                'message' => $this->message->id,
                'conversation' => $this->conversation->id
            ]);
            return;
        }
        
        // Instagram comment replies use a slightly different endpoint
        $url = "https://graph.facebook.com/v22.0/{$commentId}/replies";
        
        Log::channel('instagram')->info('Attempting to reply to Instagram comment', [
            'comment_id' => $commentId,
            'content_length' => strlen($content),
            'access_token_length' => strlen($this->page->page_access_token),
            'page_id' => $this->page->page_id
        ]);
        
        try {
            $response = Http::withToken($this->page->page_access_token)
                ->post($url, [
                    'message' => $content,
                ]);
                
            if (!$response->successful()) {
                // Log detailed error information
                $error = $response->json();
                Log::channel('instagram')->error('Failed to reply to Instagram comment', [
                    'error' => $response->body(),
                    'status' => $response->status(),
                    'comment_id' => $commentId,
                    'url' => $url,
                    'error_type' => $error['error']['type'] ?? 'unknown',
                    'error_code' => $error['error']['code'] ?? 0,
                    'error_message' => $error['error']['message'] ?? 'No specific error message'
                ]);
                
                // Special handling for permission errors
                if (isset($error['error']['code']) && $error['error']['code'] == 230) {
                    Log::channel('instagram')->error('PERMISSION ERROR: Instagram comments requires pages_manage_metadata and instagram_basic permissions', [
                        'solution' => 'Go to developers.facebook.com → App Review → Permissions and Features → Add necessary permissions'
                    ]);
                }
            } else {
                Log::channel('instagram')->info('Successfully replied to Instagram comment', [
                    'comment_id' => $commentId,
                    'response' => $response->json()
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('instagram')->error('Exception replying to Instagram comment', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function response(): array {
        // Prepare the conversation messages
        $messages = [[
            'role' => 'system',
            'content' => $this->system()
        ]];

        ConversationMessage::query()
            ->where('conversation_id', $this->conversation->id)
            ->orderByDesc('id')
            ->limit(data_get($this->general, 'conversation_memory', 3))
            ->get()
            ->reverse()
            ->each(function ($message) use (&$messages) {
                $messages[] = [
                    'role' => str($message->role->label())->lower()->__toString(),
                    'content' => $message->message
                ];
            });

        $parameters = [
            'model' => 'meta-llama/llama-4-maverick-17b-128e-instruct',
            'temperature' => (float) data_get($this->general, 'temperature', 0.5),
            'messages' => $messages,
            'max_tokens' => (int) data_get($this->general, 'max_tokens', 500)
        ];

        $groq = new Groq(config('services.groq.api_key'));

        try {
            // Generate Groq response
            $result = $groq->chat()->completions()->create($parameters);
            
            $content = $result['choices'][0]['message']['content'];
            $totalTokens = $result['usage']['total_tokens'] ?? 0;
            
        } catch (GroqException $e) {
            $fallbackResponse = data_get($this->general, 'fallback_response');

            $content = $fallbackResponse ?: "I'm sorry, but I encountered an issue while processing your request. Please try again later.";

            $totalTokens = 0;
        } catch (\Exception $e) {
            $fallbackResponse = data_get($this->general, 'fallback_response');

            $content = $fallbackResponse ?: "I'm sorry, but I encountered an issue while processing your request. Please try again later.";

            $totalTokens = 0;
        }

        return [
            'content' => $content,
            'total_tokens' => $totalTokens,
        ];
    }

    protected function system(): string {
        // Get relevant context IDs using RAG
        $relevantContexts = $this->ragService->getRelevantContexts($this->message->message, $this->workspace);
        $relevantContexts = is_array($relevantContexts) && isset($relevantContexts['no_match']) ? [] : $relevantContexts;

        $system = [];

        $system[] = '### Instructions:';
        $system[] = data_get($this->general, 'instructions');

        $businessName = data_get($this->business, 'name');
        $businessDescription = data_get($this->business, 'description');
        
        if (!empty($businessName) || !empty($businessDescription)) {
            $system[] = '### Business Information:';

            if (!empty($businessName)) {
                $system[] = '- Business Name: ' . $businessName;
            }

            if (!empty($businessDescription)) {
                $system[] = '- Business Description: ' . $businessDescription;
            }

            $system[] = PHP_EOL;
        }

        $customRules = data_get($this->general, 'custom_rules');
        if (!empty($customRules)) {
            $system[] = PHP_EOL;
            $system[] = '### Custom Rules:';
            $system[] = $customRules;
        }

        $system[] = PHP_EOL;
        $system[] = '### Core Guidelines:';

        $system[] = match (data_get($this->general, 'tone', 'professional')) {
            'professional' => '- Maintain a professional, business-like tone.',
            'friendly' => '- Maintain a friendly, approachable tone.',
            'casual' => '- Maintain a casual, relaxed tone.',
            'formal' => '- Maintain a formal, respectful tone.',
        };

        $system[] = match (data_get($this->general, 'response_length', 'concise')) {
            'concise' => '- Keep responses very concise, ideally 1-2 sentences.',
            'moderate' => '- Keep responses moderately sized, typically 2-4 sentences.',
            'detailed' => '- Provide detailed responses with thorough explanations.',
        };

        $system[] = match (data_get($this->general, 'message_style', 'direct')) {
            'direct' => '- Provide direct answers without meta-commentary.',
            'conversational' => '- Use a conversational style with natural language flow.',
            'educational' => '- Use an educational style that explains concepts clearly.',
        };

        $system[] = match (data_get($this->general, 'creativity_level', 'balanced')) {
            'strict' => '- Stick strictly to facts and the knowledge base with minimal interpretation.',
            'balanced' => '- Balance factual information with helpful interpretations.',
            'creative' => '- Use creative explanations and analogies while maintaining accuracy.',
        };

        if (data_get($this->general, 'knowledge_limitations', true)) {
            $system[] = '- If unsure about something, acknowledge the uncertainty explicitly.';
            $system[] = '- Only answer questions that are directly related to the provided knowledge base!';
            $system[] = '- If a question is outside the knowledge base scope, politely explain that you can only answer questions about the provided knowledge base!';

            $fallbackResponse = data_get($this->general, 'fallback_response');
            if (!empty($fallbackResponse)) {
                $system[] = '- When you don\'t know the answer, respond with something similar to: "' . $fallbackResponse . '"';
            }
        }

        // Formatting preferences
        $system[] = '- Format responses using Markdown when appropriate.';
        $system[] = '- Avoid word-for-word translation, rephrase to sound natural.';
        $system[] = '- Use proper ' . ($this->workspace->language->name ?? 'English') . ' grammar, punctuation and diacritical marks.';
        $system[] = '- Stay within the provided context and knowledge base.';
        $system[] = '- Keep in mind that this is an Instagram conversation.';

        $system[] = PHP_EOL;
        $system[] = '### Response Requirements:';
        $system[] = '- Keep responses focused and relevant to the user\'s query.';
        $system[] = '- Include specific examples from the knowledge base when applicable.';
        $system[] = '- DO NOT HALLUCINATE!';

        if (data_get($this->general, 'knowledge_limitations', true)) {
            $system[] = '- It\'s IMPORTANT to only answer questions that are directly related to the provided knowledge base!';
        }

        $system[] = PHP_EOL;
        $system[] = '### Knowledge Base:';
        
        // Filter contexts based on relevance
        $contextArray = $this->workspace->knowledgeBases->filter(function ($context) use ($relevantContexts) {
            return in_array($context->id, $relevantContexts);
        });
        
        // Add relevant contexts to system prompt
        $contextArray->each(function ($context) use (&$system) {
            if (empty($context->context)) {
                $system[] = '- Question: ' . $context->question;
                $system[] = '- Answer: ' . $context->answer;
            } else {
                $system[] = '- ' . $context->context;
            }
        });

        return collect($system)->implode(PHP_EOL);
    }

    /**
     * Check if Instagram integration is properly configured
     * This can be called periodically to verify settings
     */
    public static function verifySetup(InstagramPage $page) {
        $baseUrl = "https://graph.facebook.com/v22.0/";
        
        // Log basic page info
        Log::channel('instagram')->info('Verifying Instagram page setup', [
            'page_id' => $page->page_id,
            'page_name' => $page->page_name,
            'has_token' => !empty($page->page_access_token),
            'token_length' => strlen($page->page_access_token ?? ''),
            'is_active' => $page->is_active
        ]);
        
        // Skip verification if page is not active
        if (!$page->is_active) {
            Log::channel('instagram')->info('Instagram page is not active, skipping verification');
            return false;
        }
        
        // If we don't have a token or page ID, setup is incomplete
        if (empty($page->page_access_token) || empty($page->page_id)) {
            Log::channel('instagram')->error('Instagram setup incomplete', [
                'missing_token' => empty($page->page_access_token),
                'missing_page_id' => empty($page->page_id),
                'setup_url' => route('app.workspace.platforms.instagram.setup', ['uuid' => $page->workspace->uuid ?? 'unknown'])
            ]);
            return false;
        }
        
        try {
            // Check if token is valid by making a request to /me endpoint
            $response = Http::withToken($page->page_access_token)
                ->get("{$baseUrl}{$page->page_id}");
            
            if (!$response->successful()) {
                Log::channel('instagram')->error('Instagram token validation failed', [
                    'error' => $response->body(),
                    'status' => $response->status(),
                ]);
                return false;
            }
            
            // Check permissions by attempting to get page info
            $permissionsResponse = Http::withToken($page->page_access_token)
                ->get("{$baseUrl}{$page->page_id}/permissions");
            
            if ($permissionsResponse->successful()) {
                $permissions = $permissionsResponse->json();
                Log::channel('instagram')->info('Instagram permissions check', [
                    'permissions' => $permissions,
                ]);
                
                // Look for messaging permission
                $hasMessagingPermission = false;
                if (isset($permissions['data']) && is_array($permissions['data'])) {
                    foreach ($permissions['data'] as $permission) {
                        if (($permission['permission'] ?? '') === 'pages_messaging' && 
                            ($permission['status'] ?? '') === 'granted') {
                            $hasMessagingPermission = true;
                            break;
                        }
                    }
                }
                
                if (!$hasMessagingPermission) {
                    Log::channel('instagram')->warning('Instagram page is missing pages_messaging permission', [
                        'solution' => 'Add pages_messaging permission through App Review'
                    ]);
                }
            }
            
            Log::channel('instagram')->info('Instagram setup is valid');
            return true;
            
        } catch (\Exception $e) {
            Log::channel('instagram')->error('Exception during Instagram setup verification', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
