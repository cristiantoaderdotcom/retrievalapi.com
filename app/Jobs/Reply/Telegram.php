<?php

namespace App\Jobs\Reply;

use App\Enums\ConversationRole;
use App\Models\Workspace;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\TelegramBot;
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

class Telegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Conversation $conversation;
    public ConversationMessage $message;
    private Workspace $workspace;
    private TelegramBot $bot;
    private RagService $ragService;

    public array $general;
    public array $business;
    public array $styling;
    public array $platform_telegram;

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

        $this->general = $this->workspace->setting('general');
        $this->business = $this->workspace->setting('business');
        $this->styling = $this->workspace->setting('styling');
        $this->platform_telegram = $this->workspace->setting('platform_telegram') ?? [];
        
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

        // Send response to Telegram
        $this->sendMessageToTelegram($response['content']);

        return $message;
    }

    /**
     * Send message to Telegram
     */
    private function sendMessageToTelegram(string $content) {
        $url = "https://api.telegram.org/bot{$this->bot->bot_token}/sendMessage";
        
        $chatId = $this->conversation->type_source; // Should store chat ID
        
        $response = Http::post($url, [
            'chat_id' => $chatId,
            'text' => $content,
            'parse_mode' => 'Markdown'
        ]);

        if (!$response->successful()) {
            Log::channel('telegram')->error('Failed to send message to Telegram', [
                'error' => $response->body(),
                'chat_id' => $chatId
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
        $system[] = '- Keep in mind that this is a Telegram conversation.';

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
