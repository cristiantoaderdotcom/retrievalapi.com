<?php

namespace App\Http\Api\Controllers\Response;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use App\Models\ApiToken;
use App\Models\Workspace;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Enums\ConversationRole;
use LucianoTonet\GroqPHP\Groq;
use LucianoTonet\GroqPHP\GroqException;
use App\Services\AiService;
use App\Jobs\Reply\Api;

class MessageController extends Controller
{
    /**
     * Process a message and get an AI response.
     */
    public function process(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'message' => 'required|string',
                'conversation_uuid' => 'nullable|uuid',
            ]);

            // Process token and get workspace
            $tokenResult = $this->processToken($request);
            
            if (!$tokenResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $tokenResult['error']
                ], 401);
            }
            
            $workspace = $tokenResult['workspace'];
            
            // Get or create conversation
            $conversationCreated = false;
            $conversation = null;
            
            if (isset($validated['conversation_uuid'])) {
                $conversation = Conversation::where('uuid', $validated['conversation_uuid'])
                    ->where('workspace_id', $workspace->id)
                    ->first();
                    
                if (!$conversation) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Conversation not found'
                    ], 404);
                }
            } else {
                $conversation = Conversation::create([
                    'uuid' => Str::uuid(),
                    'workspace_id' => $workspace->id,
                    'source' => 'api',
                    'meta' => ['ip' => $request->ip()],
                ]);
                $conversationCreated = true;
            }
            
            // Log message
            Log::info('API message received', [
                'workspace_id' => $workspace->id,
                'conversation_id' => $conversation->id,
                'message' => $validated['message'],
            ]);
            
            // Create user message
            $message = $this->createMessage($conversation, $validated['message']);
            
            // Process response using Api job synchronously without queue
            $assistant = (new Api($conversation, $message))->handle();
            
            return response()->json([
                'success' => true,
                'conversation_uuid' => $conversation->uuid,
                'conversation_created' => $conversationCreated,
                'response' => $assistant->message,
                'tokens' => $assistant->total_tokens,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate response using the language model.
     */
    private function generateResponse(Workspace $workspace, Conversation $conversation, ConversationMessage $message): array
    {
        // Get workspace settings
        $general = $workspace->setting('general');
        $business = $workspace->setting('business');
        
        // Prepare the conversation messages
        $systemPrompt = $this->buildSystemPrompt($workspace, $general, $business);
        
        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ]
        ];

        // Add conversation history
        ConversationMessage::query()
            ->where('conversation_id', $conversation->id)
            ->orderByDesc('id')
            ->limit(data_get($general, 'conversation_memory', 3))
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
            'temperature' => (float) data_get($general, 'temperature', 0.5),
            'messages' => $messages,
            'max_tokens' => (int) data_get($general, 'max_tokens', 500)
        ];

        $groq = new Groq(config('services.groq.api_key'));

        try {
            // Generate Groq response
            $result = $groq->chat()->completions()->create($parameters);
            
            $content = $result['choices'][0]['message']['content'];
            $totalTokens = $result['usage']['total_tokens'] ?? 0;
        } catch (GroqException $e) {
            $fallbackResponse = data_get($general, 'fallback_response');
            $content = $fallbackResponse ?: "I'm sorry, but I encountered an issue while processing your request. Please try again later.";
            $totalTokens = 0;
        }

        return [
            'content' => $content,
            'total_tokens' => $totalTokens,
        ];
    }
    
    /**
     * Build system prompt for the AI.
     */
    private function buildSystemPrompt(Workspace $workspace, array $general, array $business): string
    {
        $system = [];

        $system[] = '### Instructions:';
        $system[] = data_get($general, 'instructions');

        $businessName = data_get($business, 'name');
        $businessDescription = data_get($business, 'description');
        
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

        $customRules = data_get($general, 'custom_rules');
        if (!empty($customRules)) {
            $system[] = PHP_EOL;
            $system[] = '### Custom Rules:';
            $system[] = $customRules;
        }

        $system[] = PHP_EOL;
        $system[] = '### Core Guidelines:';

        $system[] = match (data_get($general, 'tone', 'professional')) {
            'professional' => '- Maintain a professional, business-like tone.',
            'friendly' => '- Maintain a friendly, approachable tone.',
            'casual' => '- Maintain a casual, relaxed tone.',
            'formal' => '- Maintain a formal, respectful tone.',
        };

        $system[] = match (data_get($general, 'response_length', 'concise')) {
            'concise' => '- Keep responses very concise, ideally 1-2 sentences.',
            'moderate' => '- Keep responses moderately sized, typically 2-4 sentences.',
            'detailed' => '- Provide detailed responses with thorough explanations.',
        };

        $system[] = match (data_get($general, 'message_style', 'direct')) {
            'direct' => '- Provide direct answers without meta-commentary.',
            'conversational' => '- Use a conversational style with natural language flow.',
            'educational' => '- Use an educational style that explains concepts clearly.',
        };

        return collect($system)->implode(PHP_EOL);
    }

    protected function processToken(Request $request): array
    {
        $bearerToken = $request->bearerToken();
        
        if (!$bearerToken) {
            return [
                'success' => false,
                'token' => null,
                'workspace' => null,
                'error' => 'Bearer token is required'
            ];
        }
        
        $token = ApiToken::where('token', $bearerToken)->first();
        
        if (!$token) {
            return [
                'success' => false,
                'token' => null,
                'workspace' => null,
                'error' => 'Invalid token'
            ];
        }
        
        // Update the last_used_at timestamp
        $token->update(['last_used_at' => now()]);
        
        $workspace = $token->workspace;
        
        if (!$workspace) {
            return [
                'success' => false,
                'token' => $token,
                'workspace' => null,
                'error' => 'Workspace not found'
            ];
        }
        
        return [
            'success' => true,
            'token' => $token,
            'workspace' => $workspace,
            'error' => null
        ];
    }

    protected function createMessage(Conversation $conversation, string $content)
    {
        return ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role' => ConversationRole::USER,
            'message' => $content,
        ]);
    }

    protected function generateAiResponse(Workspace $workspace, Conversation $conversation, ConversationMessage $message)
    {
        $system = $this->getSystemPrompt($workspace);
        
        // Get conversation history
        $history = ConversationMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                return [
                    'role' => $msg->role,
                    'content' => $msg->message,
                ];
            })
            ->toArray();
        
        // Get AI provider and settings
        $provider = $workspace->settings['ai_provider'] ?? config('app.default_ai_provider');
        $model = $workspace->settings['ai_model'] ?? config('app.default_ai_model');
        $temperature = $workspace->settings['ai_temperature'] ?? config('app.default_ai_temperature');
        
        // Generate response using AiService
        $aiService = new AiService();
        $response = $aiService->sendChatCompletion(
            $provider,
            $model,
            $history,
            $system,
            $temperature
        );
        
        // Create assistant message
        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role' => ConversationRole::ASSISTANT,
            'message' => $response['content'],
            'total_tokens' => $response['total_tokens'] ?? 0,
        ]);
        
        return [
            'content' => $response['content'],
            'tokens' => $response['total_tokens'] ?? 0,
        ];
    }
}
