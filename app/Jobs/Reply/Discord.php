<?php

namespace App\Jobs\Reply;

use App\Enums\ConversationRole;
use App\Models\Workspace;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\DiscordBot;
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

class Discord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Conversation $conversation;
    public ConversationMessage $message;
    private Workspace $workspace;
    private DiscordBot $bot;
    private RagService $ragService;

    public array $general;
    public array $business;
    public array $styling;
    public array $platform_discord;

    /**
     * Create a new job instance.
     */
    public function __construct($conversation, $message, $bot, RagService $ragService = null) {
        $this->conversation = $conversation;
        $this->message = $message;
        $this->bot = $bot;
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

        Log::channel('discord')->info('Processing Discord job', [
            'conversation_id' => $this->conversation->id,
            'workspace_id' => $this->workspace->id,
            'guild_id' => $this->message->metadata['discord']['guild_id'] ?? null,
        ]);

        $this->general = $this->workspace->setting('general');
        $this->business = $this->workspace->setting('business');
        $this->styling = $this->workspace->setting('styling');
        $this->platform_discord = $this->workspace->setting('platform_discord') ?? [];
        
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
        
        try {
            $response = $this->response();
            
            Log::channel('discord')->info('Generated AI response', [
                'content_length' => strlen($response['content']),
                'tokens' => $response['total_tokens'],
                'conversation_id' => $this->conversation->id
            ]);

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

            // Send response to Discord
            $this->sendMessageToDiscord($response['content']);
            
            return $message;
        } catch (\Exception $e) {
            Log::channel('discord')->error('Error in Discord job', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'conversation_id' => $this->conversation->id
            ]);
            
            // Try to send a fallback message
            try {
                $this->sendMessageToDiscord("I'm sorry, I encountered an error while processing your request. Please try again later.");
            } catch (\Exception $innerException) {
                Log::channel('discord')->error('Failed to send fallback message', [
                    'error' => $innerException->getMessage()
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Send message to Discord
     */
    private function sendMessageToDiscord(string $content) {
        // Get interaction data from the message metadata
        $interactionData = $this->message->metadata['discord'] ?? null;
        
        if (!$interactionData || !isset($interactionData['interaction_token'])) {
            Log::channel('discord')->error('Missing interaction data', [
                'message_id' => $this->message->id,
            ]);
            return;
        }
        
        $interactionToken = $interactionData['interaction_token'];
        
        // Get application ID and bot token from configuration if not set on bot
        $applicationId = $this->bot->application_id ?? config('services.discord.application_id');
        $botToken = $this->bot->bot_token ?? config('services.discord.token');
        
        // Validate we have the required credentials
        if (empty($applicationId)) {
            Log::channel('discord')->error('Missing Discord application ID', [
                'bot_id' => $this->bot->id,
                'workspace_id' => $this->bot->workspace_id,
                'config_application_id' => config('services.discord.application_id')
            ]);
            return;
        }
        
        if (empty($botToken)) {
            Log::channel('discord')->error('Missing Discord bot token', [
                'bot_id' => $this->bot->id,
                'workspace_id' => $this->bot->workspace_id,
                'has_config_token' => !empty(config('services.discord.token'))
            ]);
            return;
        }
        
        // Log the API credentials we're using (without exposing the full token)
        Log::channel('discord')->info('Using Discord API credentials', [
            'application_id' => $applicationId,
            'has_bot_token' => !empty($botToken),
            'token_configured' => !empty(config('services.discord.token')),
            'interaction_token_prefix' => substr($interactionToken, 0, 10) . '...'
        ]);
        
        // For Discord interactions, we need to respond to the interaction
        $url = "https://discord.com/api/v10/webhooks/{$applicationId}/{$interactionToken}/messages/@original";
        
        Log::channel('discord')->info('Sending response to Discord', [
            'url' => $url,
            'content_length' => strlen($content),
            'chunked' => strlen($content) > 2000
        ]);
        
        // Discord has a message size limit, so we need to chunk the message if it's too long
        if (strlen($content) > 2000) {
            $chunks = str_split($content, 1990);
            
            // Send first chunk as edit to original response
            $firstResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bot {$botToken}"
            ])->patch($url, [
                'content' => $chunks[0] . '...'
            ]);
            
            if (!$firstResponse->successful()) {
                Log::channel('discord')->error('Failed to send first message chunk to Discord', [
                    'error' => $firstResponse->body(),
                    'status' => $firstResponse->status(),
                    'url' => $url,
                    'interaction_token' => substr($interactionToken, 0, 10) . '...'
                ]);
                return;
            } else {
                Log::channel('discord')->info('First chunk sent successfully', [
                    'status' => $firstResponse->status(),
                    'response' => substr($firstResponse->body(), 0, 100) . '...'
                ]);
            }
            
            // Send remaining chunks as follow-up messages
            $followUpUrl = "https://discord.com/api/v10/webhooks/{$applicationId}/{$interactionToken}";
            
            for ($i = 1; $i < count($chunks); $i++) {
                $prefix = ($i > 1) ? '...' : '';
                $suffix = ($i < count($chunks) - 1) ? '...' : '';
                
                $followUpResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bot {$botToken}"
                ])->post($followUpUrl, [
                    'content' => $prefix . $chunks[$i] . $suffix
                ]);
                
                if (!$followUpResponse->successful()) {
                    Log::channel('discord')->error("Failed to send chunk {$i} to Discord", [
                        'error' => $followUpResponse->body(),
                        'status' => $followUpResponse->status(),
                        'url' => $followUpUrl,
                        'interaction_token' => substr($interactionToken, 0, 10) . '...'
                    ]);
                }
            }
        } else {
            // Send the message in a single chunk
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bot {$botToken}"
            ])->patch($url, [
                'content' => $content
            ]);
            
            if (!$response->successful()) {
                Log::channel('discord')->error('Failed to send message to Discord', [
                    'error' => $response->body(),
                    'status' => $response->status(),
                    'url' => $url,
                    'interaction_token' => substr($interactionToken, 0, 10) . '...'
                ]);
            } else {
                Log::channel('discord')->info('Message sent successfully', [
                    'status' => $response->status(),
                    'response' => substr($response->body(), 0, 100) . '...'
                ]);
            }
        }
    }

    public function response(): array {
        Log::channel('discord')->info('Generating response using Groq API');
        
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
        
        if (empty(config('services.groq.api_key'))) {
            Log::channel('discord')->error('Groq API key not configured');
            return [
                'content' => "I'm sorry, but the AI service is not properly configured. Please contact the administrator.",
                'total_tokens' => 0,
            ];
        }

        try {
            Log::channel('discord')->info('Calling Groq API', [
                'model' => $parameters['model'],
                'message_count' => count($messages)
            ]);
            
            // Generate Groq response
            $result = $groq->chat()->completions()->create($parameters);
            
            $content = $result['choices'][0]['message']['content'];
            $totalTokens = $result['usage']['total_tokens'] ?? 0;
            
            Log::channel('discord')->info('Groq API response received', [
                'content_length' => strlen($content),
                'total_tokens' => $totalTokens
            ]);
            
        } catch (GroqException $e) {
            Log::channel('discord')->error('Groq API error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            $fallbackResponse = data_get($this->general, 'fallback_response');

            $content = $fallbackResponse ?: "I'm sorry, but I encountered an issue while processing your request. Please try again later.";

            $totalTokens = 0;
        } catch (\Exception $e) {
            Log::channel('discord')->error('Unexpected error during response generation', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
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
        $system[] = '- Use plain text formatting for Discord compatibility.';
        $system[] = '- Use discord formatting: **bold**, *italic*, __underline__, and ~~strikethrough~~ when appropriate.';
        $system[] = '- For code blocks, use ```language\ncode\n``` format.';
        $system[] = '- Avoid word-for-word translation, rephrase to sound natural.';
        $system[] = '- Use proper ' . ($this->workspace->language->name ?? 'English') . ' grammar, punctuation and diacritical marks.';
        $system[] = '- Stay within the provided context and knowledge base.';
        $system[] = '- Keep in mind that this is a Discord conversation.';
        $system[] = '- Be mindful of Discord\'s 2000 character limit per message.';

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
}
