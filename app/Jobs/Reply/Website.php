<?php

namespace App\Jobs\Reply;

use App\Enums\ConversationRole;
use App\Models\Workspace;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\Report;
use App\Models\BookingIntegration;
use App\Models\BookingRequest;
use App\Models\CustomApiIntegration;
use App\Models\CustomApiRequest;
use App\Services\Rag\RagService;
use Illuminate\Foundation\Queue\Queueable;
use LucianoTonet\GroqPHP\Groq;
use LucianoTonet\GroqPHP\GroqException;
use OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Website
{
    use Queueable;

    public Conversation $conversation;
	public ConversationMessage $message;
	private Workspace $workspace;
	private RagService $ragService;

    public array $general;
    public array $business;
    public array $styling;
    public array $platform_website;
    public array $agentic_refund_request;
    public array $agentic_reports;
    public array $booking_integrations;
    public array $agentic_shopping_assistant;

	// private ?ConversationLead $lead = null;

	/**
	 * Create a new job instance.
	 */
	public function __construct($conversation, $message, RagService $ragService = null) {
		$this->conversation = $conversation;
		$this->message = $message;
		$this->ragService = $ragService ?? app(RagService::class);

		$this->workspace = Workspace::query()
			->with('user', 'language', 'knowledgeBases', 'products', 'settings')
			->where('id', $this->conversation->workspace_id)
			->first();

        $this->general = $this->workspace->setting('general');
        $this->business = $this->workspace->setting('business');
        $this->styling = $this->workspace->setting('styling');
        $this->platform_website = $this->workspace->setting('platform_website');
        $this->agentic_refund_request = $this->workspace->setting('agentic_refund_request');
        $this->agentic_reports = $this->workspace->setting('agentic_reports');
        $this->booking_integrations = $this->workspace->setting('booking_integrations');
        $this->agentic_shopping_assistant = $this->workspace->setting('agentic_shopping_assistant');

		// if (!empty($this->settings['personalization']['user_recognition'])) {
		// 	$this->lead = ChatbotLead::query()
		// 		->where('conversation_id', $this->conversation->id)
		// 		->first();
		// }
        // Log::info($this->platform_website);
	}

	/**
	 * Execute the job.
	 */
	public function handle() {
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

		Log::info('Response: ' . $response['content']);

		$message = ConversationMessage::query()
			->create([
				'conversation_id' => $this->conversation->id,
				'role' => ConversationRole::ASSISTANT,
				'message' => $response['content'],
				'total_tokens' => $response['total_tokens'],
			]);

		if ($user && $user->messages_limit > 0) {
			$user->decrement('messages_limit');
		}

		return $message;
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
			'model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
			'temperature' => (float) data_get($this->general, 'temperature', 0.7),
			'messages' => $messages,
			'max_tokens' => (int) data_get($this->general, 'max_tokens', 500)
		];

		$groq = new Groq(config('services.groq.api_key'));

		try {
			// Generate Groq response
			$result = $groq->chat()->completions()->create($parameters);
			
			$content = $result['choices'][0]['message']['content'];
			$totalTokens = $result['usage']['total_tokens'] ?? 0;
			
			// Process agentic tools
			$content = $this->processAgenticTools($content);
			
		} catch (GroqException $e) {
			$fallbackResponse = data_get($this->general, 'fallback_response', '');

			$content = $fallbackResponse ?: "I'm sorry, but I encountered an issue. Please try again later.";

			$totalTokens = 0;
			
			Log::error('Groq API error: ' . $e->getMessage());
		} catch (\Exception $e) {
			$fallbackResponse = data_get($this->general, 'fallback_response', '');

			$content = $fallbackResponse ?: "I'm sorry, but I encountered an issue. Please try again later.";

			$totalTokens = 0;
			
			Log::error('Error during Groq API call: ' . $e->getMessage());
		}

		return [
			'content' => $content,
			'total_tokens' => $totalTokens,
		];
	}

	protected function system(): string {
		// Get conversation messages for context (excluding the current message)
		$conversationMessages = ConversationMessage::query()
			->where('conversation_id', $this->conversation->id)
			->where('id', '!=', $this->message->id) // Exclude current message
			->orderByDesc('id')
			->limit(data_get($this->general, 'conversation_memory', 3))
			->get()
			->reverse(); // Reverse to get chronological order
		
		Log::info('Website Job System: Retrieved conversation messages', [
			'conversation_id' => $this->conversation->id,
			'current_message_id' => $this->message->id,
			'current_message' => $this->message->message,
			'conversation_memory_limit' => data_get($this->general, 'conversation_memory', 3),
			'messages_count' => $conversationMessages->count(),
			'messages' => $conversationMessages->map(function($msg) {
				return [
					'id' => $msg->id,
					'role' => $msg->role->label(),
					'message' => $msg->message
				];
			})->toArray()
		]);
		
		// Get relevant context IDs using RAG with conversation context
		$relevantContent = $this->ragService->getRelevantContexts(
			$this->message->message, 
			$this->workspace, 
			$conversationMessages
		);
		
		Log::info('Website Job System: RAG results', [
			'relevant_content' => $relevantContent
		]);
		
		// Extract knowledge base IDs and product IDs
		$relevantKnowledgeBases = isset($relevantContent['knowledge_bases']) ? $relevantContent['knowledge_bases'] : [];
		$relevantProducts = isset($relevantContent['products']) ? $relevantContent['products'] : [];
		
		// Handle no matches case
		$noRelevantContent = isset($relevantContent['no_match']) || 
		                     (empty($relevantKnowledgeBases) && empty($relevantProducts));

        $system = [];

		$system[] = 'You are a powerful agentic AI assistant, that can answer questions about the business.';
		$system[] = 'Your main goal is to answer the USER\'s questions at each message, and to decide if any agentic tool is needed based on their question, any agentic tool should be wrapped in a <agentic_tool> tag, if the agentic_tool is not needed then just respond with the answer to the question.';

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

		// $userTimeData = $this->message->metadata['user_time'] ?? null;
		// if (!empty($userTimeData)) {
		// 	$system[] = PHP_EOL;
		// 	$system[] = '### User\'s Local Time:';
		// 	$system[] = '- Current Time: ' . data_get($userTimeData, 'full');
		// 	$system[] = '- Day of Week: ' . data_get($userTimeData, 'weekday');
		// 	$system[] = '- Month: ' . data_get($userTimeData, 'month');
		// 	$system[] = '- Date: ' . data_get($userTimeData, 'day');
		// 	$system[] = '- Year: ' . data_get($userTimeData, 'year');
		// 	$system[] = '- You can reference the user\'s current time/day in your responses when relevant and do not take other hours from the context, only use the current time.';
		// 	$system[] = PHP_EOL;
		// }

        // $userRecognition = data_get($this->platform_website, 'user_recognition');

		// if (!empty($userRecognition) && $this->lead && !empty($this->lead->name)) {
		// 	$system[] = PHP_EOL;
		// 	$system[] = '### User Information:';
		// 	$system[] = '- User\'s Name: ' . $this->lead->name;
		// 	$system[] = '- Frequently address the user by their name in responses to make the conversation more personal.';
		// 	$system[] = '- Use phrases like "Hi ' . $this->lead->name . '", "' . $this->lead->name . ', I think...", "Thanks for asking, ' . $this->lead->name . '", etc.';
		// 	$system[] = '- Adapt your responses to acknowledge that you remember who they are.';
		// 	$system[] = PHP_EOL;
		// }

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
			$system[] = '- It\'s IMPORTANT to only answer questions that are directly related to the provided knowledge base!';
			$system[] = '- EXCEPTION: You can always reference and answer questions about the conversation history provided above.';
		}

		// if (!empty($personalization['user_recognition']) && $this->lead && !empty($this->lead->name)) {
		// 	$system[] = '- Address the user by their name "' . $this->lead->name . '" at least once in your response.';
		// }

		// if (!empty($personalization['conversation_continuity'])) {
		// 	$system[] = '- Maintain conversation continuity by referencing previous messages when appropriate.';
		// }

		// Formatting preferences
		
		$system[] = '- Format responses using Markdown when appropriate.';
		$system[] = '- Avoid word-for-word translation, rephrase to sound natural.';
		$system[] = '- Use proper ' . ($this->workspace->language->name ?? 'English') . ' grammar, punctuation and diacritical marks.';
		$system[] = '- Stay within the provided context and knowledge base.';

		$system[] = PHP_EOL;
		$system[] = '### Response Requirements:';
		$system[] = '- Keep responses focused and relevant to the user\'s query.';
		$system[] = '- Include specific examples from the knowledge base when applicable.';
		$system[] = '- DO NOT HALLUCINATE!';

		// Add conversation history for context
		if (!$conversationMessages->isEmpty()) {
			$system[] = PHP_EOL;
			$system[] = '### Recent Conversation History:';
			$conversationMessages->each(function ($message) use (&$system) {
				$role = $message->role->label();
				$system[] = "- {$role}: {$message->message}";
			});
			$system[] = '- Use this conversation history to provide contextual responses and reference previous messages when relevant.';
		}

		// Add relevant knowledge bases
		if (!empty($relevantKnowledgeBases)) {
			$system[] = PHP_EOL;
			$system[] = '### Knowledge Base:';
			
			// Filter contexts based on relevance
			$contextArray = $this->workspace->knowledgeBases->filter(function ($context) use ($relevantKnowledgeBases) {
				return in_array($context->id, $relevantKnowledgeBases);
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
		}
		
		// Add relevant products
		if (!empty($relevantProducts)) {
			$system[] = PHP_EOL;
			$system[] = '### Products:';
			
			// Get relevant products
			$productArray = $this->workspace->products()
				->with(['variants', 'images', 'options', 'feed'])
				->whereIn('id', $relevantProducts)
				->get();
			
			// Add product information
			$productArray->each(function ($product) use (&$system) {
				$system[] = '- Product: ' . $product->title;
				
				// if (!empty($product->body_html)) {
				// 	$system[] = '  Description: ' . strip_tags($product->body_html);
				// }
				
				if (!empty($product->vendor)) {
					$system[] = '  Vendor: ' . $product->vendor;
				}
				
				if (!empty($product->product_type)) {
					$system[] = '  Type: ' . $product->product_type;
				}
				
				// if (!empty($product->tags) && is_array($product->tags)) {
				// 	$system[] = '  Tags: ' . implode(', ', $product->tags);
				// }

				if ($product->handle && $product->feed) {
					$domain = parse_url($product->feed->url, PHP_URL_HOST);
					if ($domain) {
						$system[] = "Product URL: https://{$domain}/products/{$product->handle}";
					}
				}
				
				// Add variants if any
				if ($product->variants->isNotEmpty()) {
					$system[] = '  Variants:';
					foreach ($product->variants as $variant) {
						$variantInfo = '    - ' . $variant->title;
						
						if ($variant->price) {
							$variantInfo .= ' - Price: ' . $variant->price;
							
							if ($variant->compare_at_price && $variant->compare_at_price > $variant->price) {
								$variantInfo .= ' (Was: ' . $variant->compare_at_price . ')';
							}
						}
						
						// if ($variant->sku) {
						// 	$variantInfo .= ' - SKU: ' . $variant->sku;
						// }
						
						$system[] = $variantInfo;
					}
				}
				
				// Add image URLs if any
				if ($product->images->isNotEmpty()) {
					$primaryImage = $product->getPrimaryImageUrlAttribute();
					if ($primaryImage) {
						$system[] = '  Image: ' . $primaryImage;
					}
				}
				
				$system[] = ''; // Empty line between products

				// log everything is displayed for that product
				
			});
		}
		
		// If no relevant content was found
		if ($noRelevantContent) {
			$system[] = PHP_EOL;
			$system[] = '### No relevant knowledge base entries or products were found for this query.';
			$system[] = '- Please inform the user that you don\'t have specific information about their query.';
			$system[] = '- Suggest they ask about general business information or other topics you might be able to help with.';
		}

		// Agentic tools
		$system[] = PHP_EOL;
		$system[] = '### Agentic Tools:';
		$system[] = '- You can use the following agentic tools to answer the user\'s question:';
		$system[] = '';
		
		// Generate refund request tool configuration
		if (!empty($this->agentic_refund_request) && data_get($this->agentic_refund_request, 'enabled', false)) {
			$system[] = "#### Refund Request Tool";
			
			// Use custom trigger phrases from configuration
			$triggerPhrases = data_get($this->agentic_refund_request, 'ai_rules.trigger_phrases', 'refund, return, money back, billing issue, charge dispute, cancel order');
			$system[] = "- Use when users want to request refunds, return products, or have billing issues";
			$system[] = "- Trigger phrases: {$triggerPhrases}";
			
			// Use custom validation instructions
			$validationInstructions = data_get($this->agentic_refund_request, 'ai_rules.validation_instructions', 'Before processing a refund request, ensure all required fields are provided and validate the sale ID format (xxx-1234). If information is missing or incorrect, politely ask the user to provide the correct details.');
			$system[] = "- Validation Instructions: {$validationInstructions}";
			
			// Use custom pre-submission message
			$preSubmissionMessage = data_get($this->agentic_refund_request, 'ai_rules.pre_submission_message', 'I understand you would like to request a refund. Let me collect the necessary information to process your request.');
			$system[] = "- Pre-submission Message: \"{$preSubmissionMessage}\"";
			
			// Generate schema requirements
			$schema = data_get($this->agentic_refund_request, 'schema', []);
			if (!empty($schema)) {
				$system[] = '- IMPORTANT: This tool requires the following information from the user:';
				$system[] = 'Ask the user to provide the information in a single message.';
				
				$fieldIndex = 1;
				foreach ($schema as $fieldName => $fieldConfig) {
					$label = data_get($fieldConfig, 'label', ucwords(str_replace('_', ' ', $fieldName)));
					$required = data_get($fieldConfig, 'required', false) ? 'REQUIRED' : 'optional';
					$validation = data_get($fieldConfig, 'validation', '');
					$placeholder = data_get($fieldConfig, 'placeholder', '');
					
					$system[] = "  {$fieldIndex}. {$label} ({$required})";
					
					if ($validation) {
						if (str_contains($validation, 'email')) {
							$system[] = "     - Must be a valid email format";
						} elseif (str_contains($validation, 'regex:')) {
							$pattern = str_replace(['regex:/', '/'], '', $validation);
							if ($pattern === '^[a-zA-Z]{3}-\d{4}$') {
								$system[] = "     - Must be in format: xxx-1234 (3 letters, hyphen, 4 numbers)";
							} else {
								$system[] = "     - Must match pattern: {$pattern}";
							}
						} elseif (str_contains($validation, 'min:')) {
							$min = str_replace('string|min:', '', $validation);
							$system[] = "     - Minimum {$min} characters";
						}
					}
					
					if ($placeholder) {
						$system[] = "     - Example: {$placeholder}";
					}
					
					$fieldIndex++;
				}
				
				// Add custom collection prompts
				$collectionPrompts = data_get($this->agentic_refund_request, 'ai_rules.collection_prompts', []);
				if (!empty($collectionPrompts)) {
					$system[] = '- CUSTOM COLLECTION PROMPTS:';
					foreach ($collectionPrompts as $fieldName => $prompt) {
						if (!empty($prompt)) {
							$label = data_get($schema, "{$fieldName}.label", ucwords(str_replace('_', ' ', $fieldName)));
							$system[] = "  - For {$label}: \"{$prompt}\"";
						}
					}
				}
				
				$system[] = '- VALIDATION RULES:';
				
				foreach ($schema as $fieldName => $fieldConfig) {
					if (data_get($fieldConfig, 'required', false)) {
						$label = data_get($fieldConfig, 'label', ucwords(str_replace('_', ' ', $fieldName)));
						$validation = data_get($fieldConfig, 'validation', '');
						
						if ($validation) {
							$system[] = "  - {$label}: {$validation}";
						}
					}
				}
				
				$system[] = '- If the user has NOT provided all required fields, ask them to provide the missing information using the custom prompts above';
				$system[] = '- If user provides incorrect format, explain the correct format and ask again';
				$system[] = '- Only use the tool when ALL required fields are provided in correct format';
				$system[] = '- IMPORTANT: Do not use the tool format until you have collected ALL required information from the user';
				$system[] = '- When you have all required fields, use the tool format with the actual values provided by the user';
				
				// Generate the tool format only if there are fields
				if (!empty($schema)) {
					$formatParts = [];
					foreach ($schema as $fieldName => $fieldConfig) {
						$placeholder = data_get($fieldConfig, 'placeholder', 'value');
						$formatParts[] = "{$fieldName}:{$placeholder}";
					}
					
					$formatString = implode('|', $formatParts);
					$system[] = "- Format: <agentic_tool>refund_request|{$formatString}</agentic_tool>";
					$system[] = '- Replace the placeholder values with the actual data provided by the user';
					$system[] = '- This will process the request with the validated information';
				} else {
					$system[] = '- No fields configured - tool cannot be used until fields are added';
				}
			}
			
			$system[] = '';
		} else {
			// Fallback message when refund tool is disabled
			$system[] = '#### No Refund Request Tool Configured';
			$system[] = '- Refund request tool is currently disabled';
			$system[] = '- Respond to refund requests by directing users to contact support directly';
			$system[] = '';
		}
		
		// Generate report tools configuration
		if (!empty($this->agentic_reports) && data_get($this->agentic_reports, 'enabled', false)) {
			$reportTypes = data_get($this->agentic_reports, 'types', []);
			$activeTypes = array_filter($reportTypes, function($type) {
				return data_get($type, 'enabled', false);
			});
			
			if (!empty($activeTypes)) {
				$system[] = "#### Report Tools";
				$system[] = "- Use when users want to report issues, submit feedback, or report problems";
				$system[] = "- Available report types:";
				$system[] = '';
				
				foreach ($activeTypes as $typeName => $typeConfig) {
					$label = data_get($typeConfig, 'label', ucwords(str_replace('_', ' ', $typeName)));
					$triggerKeywords = data_get($typeConfig, 'trigger_keywords', '');
					$confirmationMessage = data_get($typeConfig, 'confirmation_message', '');
					$rules = data_get($typeConfig, 'rules', '');
					
					$system[] = "##### {$label} ({$typeName})";
					$system[] = "- Trigger keywords: {$triggerKeywords}";
					$system[] = "- Confirmation message: \"{$confirmationMessage}\"";
					$system[] = "- Processing rules: {$rules}";
					$system[] = "- Format: <agentic_tool>report|type:{$typeName}|content:[user's report content]</agentic_tool>";
					$system[] = '';
				}
				
				$system[] = "#### Report Tool Usage:";
				$system[] = "- When user's message matches trigger keywords for any report type, show the confirmation message";
				$system[] = "- Wait for user to confirm they want to submit that type of report";
				$system[] = "- Ask user to provide detailed information about their report";
				$system[] = "- Once user provides report content, use the report tool format";
				$system[] = "- Follow the specific processing rules for each report type";
				$system[] = "- Do not use the report tool until user confirms they want to submit a report AND provides content";
				$system[] = '';
			}
		}
		
		// Generate booking integrations configuration
		$activeBookingIntegrations = BookingIntegration::forWorkspace($this->workspace->id)
			->active()
			->orderBy('created_at', 'asc')
			->get();
		
		if ($activeBookingIntegrations->isNotEmpty()) {
			$system[] = '';
			$system[] = "#### Booking Integrations";
			$system[] = "- Use when users want to book appointments, schedule meetings, or make reservations";
			$system[] = "- Available booking integrations:";
			$system[] = '';
			
			foreach ($activeBookingIntegrations as $integration) {
				$triggerKeywords = is_array($integration->trigger_keywords) 
					? implode(', ', $integration->trigger_keywords) 
					: ($integration->trigger_keywords ?? '');
				
				$system[] = "##### {$integration->name} ({$integration->platform_label})";
				$system[] = "- Trigger keywords: {$triggerKeywords}";
				$system[] = "- Confirmation message: \"{$integration->confirmation_message}\"";
				$system[] = "- AI Instructions: {$integration->ai_instructions}";
				
				if ($integration->is_default) {
					$system[] = "- This is the DEFAULT booking integration";
				}
				
				$system[] = "- Format: <agentic_tool>booking|integration_id:{$integration->id}</agentic_tool>";
				$system[] = '';
			}
			
			$system[] = "#### Booking Tool Usage:";
			$system[] = "- When user's message matches trigger keywords for any booking integration, show the confirmation message";
			$system[] = "- Use the integration that best matches the user's request, or default integration if no specific match";
			$system[] = "- Provide the booking URL from the integration configuration";
			$system[] = "- Follow the specific AI instructions for each integration";
			$system[] = "- Use the booking tool immediately when user confirms they want to book";
			$system[] = "- Do not collect customer information - just provide the booking link";
			$system[] = '';
		}

		// Generate shopping assistant configuration
		if (!empty($this->agentic_shopping_assistant) && data_get($this->agentic_shopping_assistant, 'enabled', false)) {
			$system[] = '';
			$system[] = "#### Shopping Assistant Tools";
			$system[] = "- Use when customers ask about products, need product information, or want product recommendations";
			$system[] = "- Available shopping tools:";
			$system[] = '';
			
			// Product Details tool
			if (data_get($this->agentic_shopping_assistant, 'product_details.enabled', false)) {
				$detailsConfig = $this->agentic_shopping_assistant['product_details'];
				$triggerKeywords = data_get($detailsConfig, 'trigger_keywords', '');
				$confirmationMessage = data_get($detailsConfig, 'confirmation_message', '');
				$rules = data_get($detailsConfig, 'rules', '');
				
				$system[] = "##### Product Details Tool";
				$system[] = "- Trigger keywords: {$triggerKeywords}";
				$system[] = "- Confirmation message: \"{$confirmationMessage}\"";
				$system[] = "- Usage rules: {$rules}";
				$system[] = "- Use when customers ask for specific product information, pricing, or details about a particular product";
				$system[] = "- IMPORTANT: Use this tool when customers mention a specific product name or ask about price/details of a particular item";
				$system[] = "- Format: <agentic_tool>product_details|query:[customer's product query]</agentic_tool>";
				$system[] = '';
			}
			
			// Product Recommendations tool
			if (data_get($this->agentic_shopping_assistant, 'product_recommendations.enabled', false)) {
				$recommendationsConfig = $this->agentic_shopping_assistant['product_recommendations'];
				$triggerKeywords = data_get($recommendationsConfig, 'trigger_keywords', '');
				$confirmationMessage = data_get($recommendationsConfig, 'confirmation_message', '');
				$rules = data_get($recommendationsConfig, 'rules', '');
				$maxResults = data_get($recommendationsConfig, 'max_results', 6);
				
				$system[] = "##### Product Recommendations Tool";
				$system[] = "- Trigger keywords: {$triggerKeywords}";
				$system[] = "- Confirmation message: \"{$confirmationMessage}\"";
				$system[] = "- Usage rules: {$rules}";
				$system[] = "- Max results: {$maxResults}";
				$system[] = "- Use when customers want product recommendations or are browsing";
				$system[] = "- Format: <agentic_tool>product_recommendations|query:[customer's search criteria]|max_results:{$maxResults}</agentic_tool>";
				$system[] = '';
			}
			
			$system[] = "#### Shopping Assistant Usage:";
			$system[] = "- When customer's message matches trigger keywords for any shopping tool, show the confirmation message";
			$system[] = "- Extract the product query or search criteria from the customer's message";
			$system[] = "- Use product_details for specific product inquiries (when they mention a specific product name or ask about price/details)";
			$system[] = "- Use product_recommendations for browsing, suggestions, or general product searches";
			$system[] = "- CRITICAL: If a customer asks about price, cost, details, or information about a specific product (especially with product name), ALWAYS use product_details tool";
			$system[] = "- Examples that should use product_details: 'price for [Product Name]', 'tell me about [Product Name]', 'how much is [Product Name]'";
			$system[] = "- The tools will automatically generate visual HTML product cards for the customer";
			$system[] = "- Do not use both tools simultaneously - choose the most appropriate one based on the customer's intent";
			$system[] = '';
		}

		// Generate custom API integrations configuration
		$activeCustomApiIntegrations = CustomApiIntegration::forWorkspace($this->workspace->id)
			->active()
			->orderBy('created_at', 'asc')
			->get();
		
		if ($activeCustomApiIntegrations->isNotEmpty()) {
			$system[] = '';
			$system[] = "#### Custom API Integrations";
			$system[] = "- Use when users need data retrieval or data submission through external APIs";
			$system[] = "- Available custom API integrations:";
			$system[] = '';
			
			foreach ($activeCustomApiIntegrations as $integration) {
				// Handle trigger_keywords which can be array or string
				$triggerKeywords = $integration->trigger_keywords ?? '';
				if (is_array($triggerKeywords)) {
					$triggerKeywords = implode(', ', $triggerKeywords);
				}
				
				$actionType = $integration->action_type === 'get_data' ? 'Data Retrieval' : 'Data Submission';
				
				$system[] = "##### {$integration->name} ({$actionType})";
				$system[] = "- Trigger keywords: {$triggerKeywords}";
				$system[] = "- Confirmation message: \"{$integration->confirmation_message}\"";
				$system[] = "- Pre-submission message: \"" . data_get($integration->ai_rules, 'pre_submission_message', '') . "\"";
				$system[] = "- Action type: {$integration->action_type}";
				
				// Detect path parameters in the URL
				$pathParameters = [];
				if (preg_match_all('/\{([^}]+)\}/', $integration->api_url, $pathMatches)) {
					$pathParameters = $pathMatches[1];
				}
				
				// Add input field requirements
				$inputSchema = $integration->input_schema ?? [];
				
				// Combine path parameters and input schema for required information
				$allRequiredFields = [];
				
				// Add path parameters as required fields
				foreach ($pathParameters as $param) {
					$allRequiredFields[$param] = [
						'label' => ucwords(str_replace('_', ' ', $param)),
						'required' => true,
						'type' => 'path_parameter',
						'placeholder' => "Enter {$param}"
					];
				}
				
				// Add input schema fields
				foreach ($inputSchema as $fieldName => $fieldConfig) {
					$allRequiredFields[$fieldName] = $fieldConfig;
				}
				
				if (!empty($allRequiredFields)) {
					$system[] = "- Required information from user:";
					$fieldIndex = 1;
					foreach ($allRequiredFields as $fieldName => $fieldConfig) {
						$label = data_get($fieldConfig, 'label', ucwords(str_replace('_', ' ', $fieldName)));
						$required = data_get($fieldConfig, 'required', false) ? 'REQUIRED' : 'optional';
						$placeholder = data_get($fieldConfig, 'placeholder', '');
						$type = data_get($fieldConfig, 'type', 'text');
						
						$system[] = "  {$fieldIndex}. {$label} ({$required})";
						if ($type === 'path_parameter') {
							$system[] = "     - This is a URL path parameter that will be inserted into the API endpoint";
						}
						if ($placeholder) {
							$system[] = "     - Example: {$placeholder}";
						}
						$fieldIndex++;
					}
				}
				
				// Show the API URL pattern
				if (!empty($pathParameters)) {
					$system[] = "- API URL Pattern: {$integration->api_url}";
					$system[] = "- Path parameters detected: " . implode(', ', array_map(function($p) { return "{{$p}}"; }, $pathParameters));
				}
				
				// Generate the tool format
				if (!empty($allRequiredFields)) {
					$formatParts = [];
					foreach ($allRequiredFields as $fieldName => $fieldConfig) {
						$placeholder = data_get($fieldConfig, 'placeholder', 'value');
						$formatParts[] = "{$fieldName}:{$placeholder}";
					}
					$formatString = implode('|', $formatParts);
					$system[] = "- Format: <agentic_tool>custom_api|integration_id:{$integration->id}|{$formatString}</agentic_tool>";
				} else {
					$system[] = "- Format: <agentic_tool>custom_api|integration_id:{$integration->id}</agentic_tool>";
				}
				
				$system[] = '';
			}
			
			$system[] = "#### Custom API Tool Usage:";
			$system[] = "- When user's message matches trigger keywords for any custom API integration, show the confirmation message";
			$system[] = "- Collect all required input fields AND path parameters from the user before making the API call";
			$system[] = "- Show the pre-submission message before executing the API request";
			$system[] = "- Use the integration that best matches the user's request";
			$system[] = "- For URLs with path parameters (like {order_id}), make sure to collect those values from the user";
			$system[] = "- For data retrieval: present the API response data in a user-friendly format";
			$system[] = "- For data submission: confirm the successful submission to the user";
			$system[] = "- Do not use the tool until all required information including path parameters is collected";
			$system[] = '';
		}

		$system[] = '#### Tool Usage Guidelines:';
		$system[] = '- Only use tools when the user\'s request clearly matches the trigger scenarios';
		$system[] = '- Always provide a brief explanation before using any tool';
		$system[] = '- If no tools are needed, respond normally with information from the knowledge base';

		//log the system
		Log::info('System: ' . collect($system)->implode(PHP_EOL));

		return collect($system)->implode(PHP_EOL);
	}

	protected function processAgenticTools($content) {
		// Process shopping assistant tools
		if (!empty($this->agentic_shopping_assistant) && data_get($this->agentic_shopping_assistant, 'enabled', false)) {
			// Process product details tool
			if (preg_match('/<agentic_tool>product_details\|query:([^<]+)<\/agentic_tool>/', $content, $matches)) {
				$query = trim($matches[1]);
				
				Log::info("Processing product_details agentic tool", ['query' => $query]);
				
				$html = $this->generateProductDetailsHtml($query);
				return str_replace($matches[0], $html, $content);
			}
			
			// Process product recommendations tool
			if (preg_match('/<agentic_tool>product_recommendations\|query:([^|]+)\|max_results:(\d+)<\/agentic_tool>/', $content, $matches)) {
				$query = trim($matches[1]);
				$maxResults = (int) trim($matches[2]);
				
				Log::info("Processing product_recommendations agentic tool", [
					'query' => $query,
					'max_results' => $maxResults
				]);
				
				$html = $this->generateProductRecommendationsHtml($query, $maxResults);
				return str_replace($matches[0], $html, $content);
			}
		}
		
		// Process refund request tool
		if (!empty($this->agentic_refund_request) && data_get($this->agentic_refund_request, 'enabled', false)) {
			$schema = data_get($this->agentic_refund_request, 'schema', []);
			if (!empty($schema)) {
				// First, check for any refund_request tool usage (even partial)
				if (preg_match('/<agentic_tool>refund_request\|([^<]+)<\/agentic_tool>/', $content, $generalMatches)) {
					$toolContent = $generalMatches[1];
					
					// Parse the tool content to extract field values
					$extractedData = [];
					$fieldPairs = explode('|', $toolContent);
					
					foreach ($fieldPairs as $pair) {
						if (strpos($pair, ':') !== false) {
							list($fieldName, $value) = explode(':', $pair, 2);
							$extractedData[trim($fieldName)] = trim($value);
						}
					}
					
					// Check if we have all required fields
					$missingRequired = [];
					foreach ($schema as $fieldName => $fieldConfig) {
						if (data_get($fieldConfig, 'required', false)) {
							if (empty($extractedData[$fieldName])) {
								$label = data_get($fieldConfig, 'label', ucwords(str_replace('_', ' ', $fieldName)));
								$missingRequired[] = $label;
							}
						}
					}
					
					// If we're missing required fields, ask for them
					if (!empty($missingRequired)) {
						$missingMessage = "I need a bit more information to process your refund request. Please provide:\n\n";
						foreach ($missingRequired as $field) {
							$missingMessage .= "• {$field}\n";
						}
						$missingMessage .= "\nPlease provide this information so I can complete your refund request.";
						
						return str_replace($generalMatches[0], $missingMessage, $content);
					}
					
					// Validate the extracted data
					$validationErrors = $this->validateRefundRequestData($extractedData, $schema);
					
					if (!empty($validationErrors)) {
						// Return validation error message
						$errorMessage = "I'm sorry, but there are some issues with the information provided:\n\n";
						foreach ($validationErrors as $error) {
							$errorMessage .= "• {$error}\n";
						}
						$errorMessage .= "\nPlease provide the correct information and try again.";
						
						return str_replace($generalMatches[0], $errorMessage, $content);
					}
					
					// All validations passed, process the refund request
					Log::info("Processing refund request agentic tool", ['data' => $extractedData]);
					
					// Create confirmation markdown
					$markdown = $this->createRefundRequestConfirmation($extractedData, $schema);
					
					return str_replace($generalMatches[0], $markdown, $content);
				}
				
				// Legacy specific pattern matching (keeping for backward compatibility)
				$fieldPatterns = [];
				foreach ($schema as $fieldName => $fieldConfig) {
					$fieldPatterns[] = "{$fieldName}:([^|]*)"; // Changed from ([^|]+) to ([^|]*) to allow empty values
				}
				$pattern = '/<agentic_tool>refund_request\|' . implode('\|', $fieldPatterns) . '<\/agentic_tool>/';
				
				if (preg_match($pattern, $content, $matches)) {
					// Extract field values
					$extractedData = [];
					$fieldIndex = 1;
					foreach ($schema as $fieldName => $fieldConfig) {
						$extractedData[$fieldName] = trim($matches[$fieldIndex]);
						$fieldIndex++;
					}
					
					// Check if we have enough data to proceed (at least one required field filled)
					$hasRequiredData = false;
					foreach ($schema as $fieldName => $fieldConfig) {
						if (data_get($fieldConfig, 'required', false) && !empty($extractedData[$fieldName])) {
							$hasRequiredData = true;
							break;
						}
					}
					
					// If no required fields are filled, don't process yet
					if (!$hasRequiredData) {
						return $content; // Let the AI continue collecting information
					}
					
					// Validate extracted data
					$validationErrors = $this->validateRefundRequestData($extractedData, $schema);
					
					if (!empty($validationErrors)) {
						// Return validation error message
						$errorMessage = "I'm sorry, but there are some issues with the information provided:\n\n";
						foreach ($validationErrors as $error) {
							$errorMessage .= "• {$error}\n";
						}
						$errorMessage .= "\nPlease provide the correct information and try again.";
						
						return str_replace($matches[0], $errorMessage, $content);
					}
					
					// All validations passed, process the refund request
					Log::info("Processing refund request agentic tool", ['data' => $extractedData]);
					
					// Create confirmation markdown
					$markdown = $this->createRefundRequestConfirmation($extractedData, $schema);
					
					return str_replace($matches[0], $markdown, $content);
				}
			}
		}
		
		// Process report tools
		if (!empty($this->agentic_reports) && data_get($this->agentic_reports, 'enabled', false)) {
			$reportTypes = data_get($this->agentic_reports, 'types', []);
			
			// Check for report tool usage: <agentic_tool>report|type:report_type|content:report_content</agentic_tool>
			if (preg_match('/<agentic_tool>report\|type:([^|]+)\|content:([^<]+)<\/agentic_tool>/', $content, $matches)) {
				$reportType = trim($matches[1]);
				$reportContent = trim($matches[2]);
				
				// Validate that the report type exists and is enabled
				if (isset($reportTypes[$reportType]) && data_get($reportTypes[$reportType], 'enabled', false)) {
					Log::info("Processing report agentic tool", [
						'type' => $reportType,
						'content' => $reportContent
					]);
					
					// Create the report confirmation
					$markdown = $this->createReportConfirmation($reportType, $reportContent, $reportTypes[$reportType]);
					
					return str_replace($matches[0], $markdown, $content);
				} else {
					// Invalid or disabled report type
					$errorMessage = "I'm sorry, but the report type '{$reportType}' is not available or has been disabled. Please try again with a different report type.";
					return str_replace($matches[0], $errorMessage, $content);
				}
			}
		}
		
		// Process booking tools
		$activeBookingIntegrations = BookingIntegration::forWorkspace($this->workspace->id)
			->active()
			->orderBy('created_at', 'asc')
			->get();
		
		if ($activeBookingIntegrations->isNotEmpty()) {
			// Check for booking tool usage: <agentic_tool>booking|integration_id:123</agentic_tool>
			if (preg_match('/<agentic_tool>booking\|integration_id:(\d+)<\/agentic_tool>/', $content, $matches)) {
				$integrationId = (int) trim($matches[1]);
				
				// Find the integration
				$integration = $activeBookingIntegrations->firstWhere('id', $integrationId);
				
				if ($integration) {
					Log::info("Processing booking agentic tool", [
						'integration_id' => $integrationId,
						'integration_name' => $integration->name,
					]);
					
					// Create the booking confirmation
					$markdown = $this->createBookingConfirmation($integration);
					
					return str_replace($matches[0], $markdown, $content);
				} else {
					// Integration not found or inactive
					$errorMessage = "I'm sorry, but the booking integration is not available at the moment. Please try again later or contact our support team.";
					return str_replace($matches[0], $errorMessage, $content);
				}
			}
		}
		
		// Process custom API integrations
		$activeCustomApiIntegrations = CustomApiIntegration::forWorkspace($this->workspace->id)
			->active()
			->orderBy('created_at', 'asc')
			->get();
		
		if ($activeCustomApiIntegrations->isNotEmpty()) {
			// Check for custom API tool usage: <agentic_tool>custom_api|integration_id:123|field1:value1|field2:value2</agentic_tool>
			if (preg_match('/<agentic_tool>custom_api\|integration_id:(\d+)(?:\|([^<]+))?<\/agentic_tool>/', $content, $matches)) {
				$integrationId = (int) trim($matches[1]);
				$fieldData = isset($matches[2]) ? trim($matches[2]) : '';
				$originalMatch = $matches[0]; // Store the original match to avoid variable conflict
				
				// Find the integration
				$integration = $activeCustomApiIntegrations->firstWhere('id', $integrationId);
				
				if ($integration) {
					Log::info("Processing custom API agentic tool", [
						'integration_id' => $integrationId,
						'integration_name' => $integration->name,
						'field_data' => $fieldData
					]);
					
					// Parse field data if provided
					$extractedData = [];
					if (!empty($fieldData)) {
						$fieldPairs = explode('|', $fieldData);
						foreach ($fieldPairs as $pair) {
							if (strpos($pair, ':') !== false) {
								list($fieldName, $value) = explode(':', $pair, 2);
								$extractedData[trim($fieldName)] = trim($value);
							}
						}
					}
					
					// Detect path parameters in the URL
					$pathParameters = [];
					if (preg_match_all('/\{([^}]+)\}/', $integration->api_url, $pathMatches)) {
						$pathParameters = $pathMatches[1];
					}
					
					// Validate required fields (including path parameters)
					$inputSchema = $integration->input_schema ?? [];
					$missingRequired = [];
					$validationErrors = [];
					
					// Check path parameters first
					foreach ($pathParameters as $param) {
						$value = data_get($extractedData, $param, '');
						$label = ucwords(str_replace('_', ' ', $param));
						
						if (empty($value)) {
							$missingRequired[] = "{$label} (URL parameter)";
						}
					}
					
					// Check input schema fields
					foreach ($inputSchema as $fieldName => $fieldConfig) {
						$required = data_get($fieldConfig, 'required', false);
						$value = data_get($extractedData, $fieldName, '');
						$label = data_get($fieldConfig, 'label', ucwords(str_replace('_', ' ', $fieldName)));
						
						if ($required && empty($value)) {
							$missingRequired[] = $label;
						}
						
						// Basic validation
						if (!empty($value)) {
							$type = data_get($fieldConfig, 'type', 'text');
							switch ($type) {
								case 'email':
									if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
										$validationErrors[] = "{$label} must be a valid email address";
									}
									break;
								case 'number':
									if (!is_numeric($value)) {
										$validationErrors[] = "{$label} must be a valid number";
									}
									break;
							}
						}
					}
					
					// If we're missing required fields, ask for them
					if (!empty($missingRequired)) {
						$missingMessage = "I need some additional information to process your request. Please provide:\n\n";
						foreach ($missingRequired as $field) {
							$missingMessage .= "• {$field}\n";
						}
						$missingMessage .= "\nOnce you provide this information, I'll be able to help you with your request.";
						
						return str_replace($originalMatch, $missingMessage, $content);
					}
					
					// If there are validation errors, return them
					if (!empty($validationErrors)) {
						$errorMessage = "I noticed some issues with the information provided:\n\n";
						foreach ($validationErrors as $error) {
							$errorMessage .= "• {$error}\n";
						}
						$errorMessage .= "\nPlease provide the correct information and try again.";
						
						return str_replace($originalMatch, $errorMessage, $content);
					}
					
					// All validations passed, make the API call
					try {
						$markdown = $this->processCustomApiIntegration($integration, $extractedData);
						return str_replace($originalMatch, $markdown, $content);
					} catch (\Exception $e) {
						Log::error("Custom API integration failed", [
							'integration_id' => $integrationId,
							'error' => $e->getMessage(),
							'data' => $extractedData
						]);
						
						$errorMessage = "I'm sorry, but I encountered an issue while processing your request. Please try again later or contact our support team if the problem persists.";
						return str_replace($originalMatch, $errorMessage, $content);
					}
				} else {
					// Integration not found or inactive
					$errorMessage = "I'm sorry, but the requested integration is not available at the moment. Please try again later or contact our support team.";
					return str_replace($originalMatch, $errorMessage, $content);
				}
			}
		}
		
		return $content;
	}
	
	protected function validateRefundRequestData($data, $schema) {
		$errors = [];
		
		foreach ($schema as $fieldName => $fieldConfig) {
			$value = data_get($data, $fieldName, '');
			$required = data_get($fieldConfig, 'required', false);
			$validation = data_get($fieldConfig, 'validation', '');
			$label = data_get($fieldConfig, 'label', ucwords(str_replace('_', ' ', $fieldName)));
			
			// Check if required field is empty
			if ($required && empty($value)) {
				$errors[] = "{$label} is required";
				continue;
			}
			
			// Skip validation if field is empty and not required
			if (empty($value) && !$required) {
				continue;
			}
			
			// Validate based on validation rules only if value is not empty
			if (!empty($validation) && !empty($value)) {
				if (str_contains($validation, 'email')) {
					if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
						$errors[] = "{$label} must be a valid email address";
					}
				} elseif (str_contains($validation, 'regex:')) {
					$pattern = str_replace(['regex:', '/'], '', $validation);
					if (!preg_match("/{$pattern}/", $value)) {
						if ($pattern === '^[a-zA-Z]{3}-\d{4}$') {
							$errors[] = "{$label} must be in format xxx-1234 (3 letters, hyphen, 4 numbers)";
						} else {
							$errors[] = "{$label} format is invalid";
						}
					}
				} elseif (str_contains($validation, 'min:')) {
					$minLength = (int) str_replace(['string|min:', 'min:'], '', $validation);
					if (strlen($value) < $minLength) {
						$errors[] = "{$label} must be at least {$minLength} characters long";
					}
				}
			}
		}
		
		return $errors;
	}
	
	protected function createRefundRequestConfirmation($data, $schema) {
		// Store the request in the database
		try {
			RefundRequest::create([
				'conversation_id' => $this->conversation->id,
				'request_data' => $data,
				'status' => RefundRequest::STATUS_PENDING,
			]);
			
			Log::info("Refund request stored successfully", [
				'conversation_id' => $this->conversation->id,
				'data' => $data
			]);
		} catch (\Exception $e) {
			Log::error("Failed to store refund request", [
				'error' => $e->getMessage(),
				'conversation_id' => $this->conversation->id,
				'data' => $data
			]);
		}
		
		// Get custom success response configuration
		$successConfig = data_get($this->agentic_refund_request, 'success_response', []);
		$title = data_get($successConfig, 'title', 'Refund Request Submitted Successfully');
		$message = data_get($successConfig, 'message', 'Your refund request has been submitted and our support team will review it within 24-48 hours. You will receive a confirmation email shortly.');
		$additionalInfo = data_get($successConfig, 'additional_info', '');
		$showDetails = data_get($successConfig, 'show_details', true);
		
		// Generate confirmation markdown
		$markdown = "✅ **{$title}**\n\n";
		$markdown .= "{$message}\n\n";
		
		if ($showDetails) {
			$markdown .= "**Request Details:**\n";
			foreach ($data as $fieldName => $value) {
				$label = data_get($schema, "{$fieldName}.label", ucwords(str_replace('_', ' ', $fieldName)));
				$markdown .= "• **{$label}:** {$value}\n";
			}
			$markdown .= "\n";
		}
		
		if (!empty($additionalInfo)) {
			$markdown .= "{$additionalInfo}";
		}
		
		return $markdown;
	}

	protected function createLegacyRefundConfirmation($email, $saleId) {
		// Store the refund request in the database (legacy format)
		try {
			RefundRequest::create([
				'conversation_id' => $this->conversation->id,
				'request_data' => [
					'email' => $email,
					'sale_id' => $saleId
				],
				'status' => RefundRequest::STATUS_PENDING,
			]);
			
			Log::info('Legacy refund request stored successfully', [
				'conversation_id' => $this->conversation->id,
				'email' => $email,
				'sale_id' => $saleId
			]);
		} catch (\Exception $e) {
			Log::error('Failed to store legacy refund request', [
				'error' => $e->getMessage(),
				'conversation_id' => $this->conversation->id,
				'email' => $email,
				'sale_id' => $saleId
			]);
		}
		
		// Get custom success response configuration
		$successConfig = data_get($this->agentic_refund_request, 'success_response', []);
		$title = data_get($successConfig, 'title', 'Refund Request Submitted Successfully');
		$message = data_get($successConfig, 'message', 'Your refund request has been submitted and our support team will review it within 24-48 hours. You will receive a confirmation email shortly.');
		$additionalInfo = data_get($successConfig, 'additional_info', '');
		$showDetails = data_get($successConfig, 'show_details', true);
		
		// Generate markdown confirmation using user's configured text
		$markdown = "✅ **{$title}**\n\n";
		$markdown .= "{$message}\n\n";
		
		if ($showDetails) {
			$markdown .= "**Request Details:**\n";
			$markdown .= "• **Email:** {$email}\n";
			$markdown .= "• **Sale ID:** {$saleId}\n\n";
		}
		
		if (!empty($additionalInfo)) {
			$markdown .= "{$additionalInfo}";
		}
		
		return $markdown;
	}

	protected function createReportConfirmation($reportType, $reportContent, $typeConfig) {
		// Store the report in the database
		try {
			Report::create([
				'conversation_id' => $this->conversation->id,
				'report_type' => $reportType,
				'report_content' => $reportContent,
				'status' => Report::STATUS_PENDING,
			]);
			
			Log::info("Report stored successfully", [
				'conversation_id' => $this->conversation->id,
				'type' => $reportType,
				'content' => $reportContent
			]);
		} catch (\Exception $e) {
			Log::error("Failed to store report", [
				'error' => $e->getMessage(),
				'conversation_id' => $this->conversation->id,
				'type' => $reportType,
				'content' => $reportContent
			]);
		}
		
		// Get report type configuration
		$label = data_get($typeConfig, 'label', ucwords(str_replace('_', ' ', $reportType)));
		
		// Generate confirmation markdown
		$markdown = "✅ **{$label} Submitted Successfully**\n\n";
		$markdown .= "Thank you for submitting your {$label}. Our team has received your report and will investigate it promptly.\n\n";
		$markdown .= "**Report Details:**\n";
		$markdown .= "• **Type:** {$label}\n";
		$markdown .= "• **Content:** {$reportContent}\n";
		$markdown .= "• **Status:** Pending Investigation\n\n";
		$markdown .= "You can expect a response from our team within 24-48 hours. If this is an urgent security issue, please also contact our support team directly.";
		
		return $markdown;
	}

	protected function createBookingConfirmation($integration) {
		// Store the booking request in the database
		try {
			$bookingUrl = data_get($integration->configuration, 'booking_url', '');
			
			$bookingRequest = BookingRequest::create([
				'conversation_id' => $this->conversation->id,
				'booking_integration_id' => $integration->id,
				'status' => BookingRequest::STATUS_PENDING,
				'booking_url' => $bookingUrl,
			]);
			
			Log::info("Booking request stored successfully", [
				'conversation_id' => $this->conversation->id,
				'integration_id' => $integration->id,
				'booking_request_id' => $bookingRequest->id
			]);
		} catch (\Exception $e) {
			Log::error("Failed to store booking request", [
				'error' => $e->getMessage(),
				'conversation_id' => $this->conversation->id,
				'integration_id' => $integration->id,
			]);
		}
		
		// Generate confirmation markdown
		$markdown = "📅 **Booking Request Submitted Successfully**\n\n";
		$markdown .= "Thank you for your booking request! I've prepared your booking link for our {$integration->platform_label} system.\n\n";
		
		// Provide booking URL if available
		$bookingUrl = data_get($integration->configuration, 'booking_url', '');
		if ($bookingUrl) {
			$markdown .= "**Next Steps:**\n";
			$markdown .= "1. Click the link below to access our booking system\n";
			$markdown .= "2. Select your preferred date and time\n";
			$markdown .= "3. Complete your appointment booking\n\n";
			$markdown .= "🔗 **[Book Your Appointment Here]({$bookingUrl})**\n\n";
		}
		
		$markdown .= "You will receive a confirmation email once your appointment is scheduled. If you have any questions or need assistance with booking, please don't hesitate to contact us.";
		
		return $markdown;
	}

	protected function processCustomApiIntegration($integration, $data) {
		// Store the request in the database first
		try {
			$apiRequest = CustomApiRequest::create([
				'conversation_id' => $this->conversation->id,
				'custom_api_integration_id' => $integration->id,
				'request_data' => $data,
				'status' => CustomApiRequest::STATUS_PENDING,
			]);
			
			Log::info("Custom API request stored successfully", [
				'conversation_id' => $this->conversation->id,
				'integration_id' => $integration->id,
				'request_id' => $apiRequest->id,
				'data' => $data
			]);
		} catch (\Exception $e) {
			Log::error("Failed to store custom API request", [
				'error' => $e->getMessage(),
				'conversation_id' => $this->conversation->id,
				'integration_id' => $integration->id,
				'data' => $data
			]);
			throw $e;
		}
		
		// Prepare API request with optimized timeout settings
		$url = $integration->api_url;
		$method = strtoupper($integration->http_method);
		
		// Replace path parameters in the URL
		if (preg_match_all('/\{([^}]+)\}/', $url, $matches)) {
			$pathParameters = $matches[1];
			$nonPathData = $data; // Copy for non-path parameters
			
			foreach ($pathParameters as $param) {
				$paramValue = data_get($data, $param);
				if ($paramValue !== null) {
					// Replace the {parameter} with the actual value
					$url = str_replace("{{$param}}", urlencode($paramValue), $url);
					// Remove path parameters from data since they're now in the URL
					unset($nonPathData[$param]);
				}
			}
			
			// Update data to only include non-path parameters for the request body/query
			$data = $nonPathData;
		}
		
		// Use a more conservative timeout to prevent PHP execution timeout
		// Maximum 15 seconds to leave time for processing and response generation
		$configuredTimeout = $integration->timeout ?? 30;
		$timeout = min($configuredTimeout, 15);
		
		Log::info("Making API call", [
			'integration_id' => $integration->id,
			'original_url' => $integration->api_url,
			'final_url' => $url,
			'method' => $method,
			'timeout' => $timeout,
			'configured_timeout' => $configuredTimeout,
			'data' => $data
		]);
		
		// Prepare headers
		$headers = [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'User-Agent' => 'ReplyElf-AI-Assistant/1.0',
		];
		
		// Add authentication headers
		$authConfig = $integration->auth_config ?? [];
		switch ($integration->auth_type) {
			case 'bearer':
				if (!empty($authConfig['token'])) {
					$headers['Authorization'] = 'Bearer ' . $authConfig['token'];
				}
				break;
			case 'api_key':
				if (!empty($authConfig['key']) && !empty($authConfig['value'])) {
					if (($authConfig['location'] ?? 'header') === 'header') {
						$headers[$authConfig['key']] = $authConfig['value'];
					}
				}
				break;
			case 'basic':
				if (!empty($authConfig['username']) && !empty($authConfig['password'])) {
					$headers['Authorization'] = 'Basic ' . base64_encode($authConfig['username'] . ':' . $authConfig['password']);
				}
				break;
		}
		
		// Create HTTP client with optimized settings
		$httpClient = Http::timeout($timeout)
			->connectTimeout(5) // 5 seconds for connection
			->retry(2, 100) // Retry twice with 100ms delay
			->withHeaders($headers);


			Log::info("HTTP Client", [
				'httpClient' => $httpClient
			]);
		
		// Add query parameters for API key authentication
		if ($integration->auth_type === 'api_key' && ($authConfig['location'] ?? 'header') === 'query') {
			if (!empty($authConfig['key']) && !empty($authConfig['value'])) {
				$url .= (strpos($url, '?') !== false ? '&' : '?') . $authConfig['key'] . '=' . urlencode($authConfig['value']);
			}
		}
		
		// Add query parameters for GET requests
		if ($method === 'GET' && !empty($data)) {
			$queryParams = [];
			foreach ($data as $key => $value) {
				$queryParams[$key] = $value;
			}
			$url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($queryParams);
		}
		
		try {
			$startTime = microtime(true);
			
			// Make the request based on method
			if ($method === 'GET') {
				$response = $httpClient->get($url);
			} elseif ($method === 'POST') {
				$response = $httpClient->post($url, $data);
			} elseif ($method === 'PUT') {
				$response = $httpClient->put($url, $data);
			} elseif ($method === 'PATCH') {
				$response = $httpClient->patch($url, $data);
			} elseif ($method === 'DELETE') {
				$response = $httpClient->delete($url, $data);
			} else {
				throw new \Exception("Unsupported HTTP method: {$method}");
			}
			
			$endTime = microtime(true);
			$responseTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
			
			// Update request status and response
			$responseData = $response->json();
			$statusCode = $response->status();
			
			$apiRequest->update([
				'status' => $response->successful() ? CustomApiRequest::STATUS_SUCCESS : CustomApiRequest::STATUS_FAILED,
				'response_data' => $responseData,
				'status_code' => $statusCode,
				'processed_at' => now(),
			]);
			
			Log::info("Custom API call completed", [
				'integration_id' => $integration->id,
				'request_id' => $apiRequest->id,
				'status_code' => $statusCode,
				'response_time_ms' => $responseTime,
				'successful' => $response->successful()
			]);
			
			// Generate response markdown
			if ($response->successful()) {
				return $this->createCustomApiSuccessResponse($integration, $data, $responseData);
			} else {
				throw new \Exception("API request failed with status code: {$statusCode}");
			}
			
		} catch (\Illuminate\Http\Client\ConnectionException $e) {
			// Handle connection/timeout errors specifically
			$errorMessage = "Connection timeout or network error";
			if (str_contains($e->getMessage(), 'timeout')) {
				$errorMessage = "API request timed out after {$timeout} seconds";
			} elseif (str_contains($e->getMessage(), 'Connection refused')) {
				$errorMessage = "Could not connect to API endpoint";
			}
			
			$apiRequest->update([
				'status' => CustomApiRequest::STATUS_FAILED,
				'error_message' => $errorMessage,
				'processed_at' => now(),
			]);
			
			Log::error("Custom API connection error", [
				'integration_id' => $integration->id,
				'request_id' => $apiRequest->id,
				'error' => $e->getMessage(),
				'url' => $url,
				'timeout' => $timeout
			]);
			
			// Return user-friendly error message instead of throwing
			return "⚠️ **API Request Timeout**\n\n" .
				   "I'm sorry, but the API request took longer than expected to respond. " .
				   "This might be due to:\n" .
				   "• High server load on the external API\n" .
				   "• Network connectivity issues\n" .
				   "• The API endpoint being temporarily unavailable\n\n" .
				   "Please try again in a few moments. If the issue persists, contact our support team.";
			
		} catch (\Exception $e) {
			// Handle other errors
			$apiRequest->update([
				'status' => CustomApiRequest::STATUS_FAILED,
				'error_message' => $e->getMessage(),
				'processed_at' => now(),
			]);
			
			Log::error("Custom API call failed", [
				'integration_id' => $integration->id,
				'request_id' => $apiRequest->id,
				'error' => $e->getMessage(),
				'url' => $url
			]);
			
			throw $e;
		}
	}
	
	protected function createCustomApiSuccessResponse($integration, $requestData, $responseData) {
		// Get success response configuration
		$successConfig = $integration->success_response ?? [];
		$title = data_get($successConfig, 'title', 'Information Retrieved Successfully');
		$message = data_get($successConfig, 'message', 'I\'ve successfully retrieved the information from our system.');
		$showResponseData = data_get($successConfig, 'show_response_data', true);
		
		// Generate confirmation markdown
		$markdown = "✅ **{$title}**\n\n";
		$markdown .= "{$message}\n\n";
		
		// Show request details
		if (!empty($requestData)) {
			$markdown .= "**Request Information:**\n";
			foreach ($requestData as $fieldName => $value) {
				$inputSchema = $integration->input_schema ?? [];
				$label = data_get($inputSchema, "{$fieldName}.label", ucwords(str_replace('_', ' ', $fieldName)));
				$markdown .= "• **{$label}:** {$value}\n";
			}
			$markdown .= "\n";
		}
		
		// Show API response data if enabled
		if ($showResponseData && !empty($responseData)) {
			$markdown .= "**Retrieved Information:**\n";
			
			// Format the response data nicely
			if (is_array($responseData)) {
				$markdown .= $this->formatApiResponseData($responseData);
			} else {
				$markdown .= "```\n" . json_encode($responseData, JSON_PRETTY_PRINT) . "\n```\n";
			}
		}
		
		return $markdown;
	}
	
	protected function formatApiResponseData($data, $level = 0) {
		$markdown = '';
		$indent = str_repeat('  ', $level);
		
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$markdown .= "{$indent}• **" . ucwords(str_replace('_', ' ', $key)) . ":**\n";
				$markdown .= $this->formatApiResponseData($value, $level + 1);
			} else {
				$markdown .= "{$indent}• **" . ucwords(str_replace('_', ' ', $key)) . ":** {$value}\n";
			}
		}
		
		return $markdown;
	}

	/**
	 * Generate detailed product HTML card
	 */
	protected function generateProductDetailsHtml($query) {
		Log::info("Generating product details HTML", [
			'query' => $query
		]);
		
		// Use RAG service to find the most relevant product
		$relevantContent = $this->ragService->getRelevantContexts($query, $this->workspace);
		
		Log::info("RAG service result for product details", [
			'relevant_content' => $relevantContent
		]);
		
		if (isset($relevantContent['no_match']) || empty($relevantContent['products'])) {
			// If RAG service fails, try a simple text search as fallback
			Log::info("RAG service returned no match, trying fallback search for product details");
			
			$product = $this->workspace->products()
				->with(['variants', 'images', 'options', 'feed'])
				->where(function($q) use ($query) {
					$q->where('title', 'LIKE', '%' . $query . '%')
					  ->orWhere('body_html', 'LIKE', '%' . $query . '%')
					  ->orWhere('product_type', 'LIKE', '%' . $query . '%')
					  ->orWhere('vendor', 'LIKE', '%' . $query . '%')
					  ->orWhere('tags', 'LIKE', '%' . $query . '%');
				})
				->first();
			
			if (!$product) {
				return "I'm sorry, I couldn't find the specific product you're looking for.";
			}
			
			// Generate detailed product card HTML
			$html = $this->buildDetailedProductCard($product);
			
			return "Here's the detailed information about the product you asked about:\n\n" . $html;
		}
		
		// Get the first (most relevant) product
		$productId = $relevantContent['products'][0];
		$product = $this->workspace->products()
			->with(['variants', 'images', 'options', 'feed'])
			->find($productId);
		
		if (!$product) {
			return "I'm sorry, I couldn't find the specific product you're looking for.";
		}
		
		// Generate detailed product card HTML
		$html = $this->buildDetailedProductCard($product);
		
		return "Here's the detailed information about the product you asked about:\n\n" . $html;
	}

	/**
	 * Generate product recommendations HTML cards
	 */
	protected function generateProductRecommendationsHtml($query, $maxResults) {
		Log::info("Generating product recommendations HTML", [
			'query' => $query,
			'max_results' => $maxResults
		]);
		
		// Use RAG service to find relevant products
		$relevantContent = $this->ragService->getRelevantContexts($query, $this->workspace);
		
		Log::info("RAG service result for product recommendations", [
			'relevant_content' => $relevantContent
		]);
		
		if (isset($relevantContent['no_match']) || empty($relevantContent['products'])) {
			// If RAG service fails, try a simple text search as fallback
			Log::info("RAG service returned no match, trying fallback search");
			
			$products = $this->workspace->products()
				->with(['variants', 'images', 'options', 'feed'])
				->where(function($q) use ($query) {
					$q->where('title', 'LIKE', '%' . $query . '%')
					  ->orWhere('body_html', 'LIKE', '%' . $query . '%')
					  ->orWhere('product_type', 'LIKE', '%' . $query . '%')
					  ->orWhere('vendor', 'LIKE', '%' . $query . '%')
					  ->orWhere('tags', 'LIKE', '%' . $query . '%');
				})
				->limit($maxResults)
				->get();
			
			Log::info("Fallback search result", [
				'products_found' => $products->count(),
				'product_titles' => $products->pluck('title')->toArray()
			]);
			
			if ($products->isEmpty()) {
				return "I'm sorry, I couldn't find any products matching your criteria. Please try a different search term or browse our product catalog.";
			}
			
			// Generate simple product cards HTML
			$html = $this->buildProductRecommendationsGrid($products);
			
			return "Here are some great product recommendations based on your search:\n\n" . $html;
		}
		
		// Get the requested number of products
		$productIds = array_slice($relevantContent['products'], 0, $maxResults);
		$products = $this->workspace->products()
			->with(['variants', 'images', 'options', 'feed'])
			->whereIn('id', $productIds)
			->get();
		
		if ($products->isEmpty()) {
			return "I'm sorry, I couldn't find any products matching your criteria.";
		}
		
		// Generate simple product cards HTML
		$html = $this->buildProductRecommendationsGrid($products);
		
		return "Here are some great product recommendations based on your search:\n\n" . $html;
	}

	/**
	 * Build detailed product card HTML
	 */
	protected function buildDetailedProductCard($product) {
		$imageUrl = $product->getPrimaryImageUrlAttribute() ?? 'https://via.placeholder.com/300x200/f1f5f9/64748b?text=🛍️+Product';
		$productUrl = '';
		
		// Build product URL if available
		if ($product->handle && $product->feed) {
			$domain = parse_url($product->feed->url, PHP_URL_HOST);
			if ($domain) {
				$productUrl = "https://{$domain}/products/{$product->handle}";
			}
		}
		
		$html = '<div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin: 12px 0; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); min-width: 100%; font-family: system-ui, -apple-system, sans-serif;">';
		
		// Product image
		$html .= '<div style="text-align: center; margin-bottom: 16px; background: #f8fafc; padding: 16px; border-radius: 8px;">';
		$html .= '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($product->title) . '" style="max-width: 100%; height: 200px; object-fit: cover; border-radius: 8px;" />';
		$html .= '</div>';
		
		// Product title
		$html .= '<h3 style="font-size: 18px; font-weight: bold; margin: 0 0 12px 0; color: #1f2937;">' . htmlspecialchars($product->title) . '</h3>';
		
		// Brand and category
		$html .= '<div style="margin-bottom: 12px;">';
		if (!empty($product->vendor)) {
			$html .= '<span style="background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; margin-right: 8px;">' . htmlspecialchars($product->vendor) . '</span>';
		}
		if (!empty($product->product_type)) {
			$html .= '<span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">' . htmlspecialchars($product->product_type) . '</span>';
		}
		$html .= '</div>';
		
		// Product description
		if (!empty($product->body_html)) {
			$description = strip_tags($product->body_html);
			$description = strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description;
			$html .= '<p style="color: #6b7280; margin: 0 0 16px 0; line-height: 1.5; font-size: 14px;">' . htmlspecialchars($description) . '</p>';
		}
		
		// Pricing
		if ($product->variants->isNotEmpty()) {
			$prices = $product->variants->pluck('price')->filter()->unique()->sort();
			if ($prices->isNotEmpty()) {
				$priceDisplay = $prices->count() === 1 
					? '$' . number_format($prices->first(), 2)
					: 'From $' . number_format($prices->first(), 2);
				$html .= '<div style="background: #059669; color: white; padding: 12px; border-radius: 8px; text-align: center; margin: 16px 0;">';
				$html .= '<div style="font-size: 20px; font-weight: bold;">' . $priceDisplay . '</div>';
				if ($prices->count() > 1) {
					$html .= '<div style="font-size: 12px; opacity: 0.9;">Multiple variants available</div>';
				}
				$html .= '</div>';
			}
		}
		
		// Product URL button
		if ($productUrl) {
			$html .= '<div style="text-align: center; margin-top: 16px;">';
			$html .= '<a href="' . htmlspecialchars($productUrl) . '" target="_blank" style="display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;">🛍️ View Product Details</a>';
			$html .= '</div>';
		}
		
		$html .= '</div>';
		
		return $html;
	}

	/**
	 * Build product recommendations grid HTML
	 */
	protected function buildProductRecommendationsGrid($products) {
		$html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin: 12px 0; font-family: system-ui, -apple-system, sans-serif;">';
		
		foreach ($products as $product) {
			$imageUrl = $product->getPrimaryImageUrlAttribute() ?? 'https://via.placeholder.com/150x120/f1f5f9/64748b?text=🛒+Item';
			$productUrl = '';
			
			// Build product URL if available
			if ($product->handle && $product->feed) {
				$domain = parse_url($product->feed->url, PHP_URL_HOST);
				if ($domain) {
					$productUrl = "https://{$domain}/products/{$product->handle}";
				}
			}
			
			$html .= '<div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; background: white; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';
			
			// Product image
			$html .= '<div style="margin-bottom: 12px; background: #f8fafc; padding: 8px; border-radius: 8px;">';
			$html .= '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($product->title) . '" style="width: 100%; height: 100px; object-fit: cover; border-radius: 6px;" />';
			$html .= '</div>';
			
			// Product title (truncated)
			$title = strlen($product->title) > 40 ? substr($product->title, 0, 40) . '...' : $product->title;
			$html .= '<h4 style="font-size: 14px; font-weight: bold; margin: 0 0 8px 0; color: #1f2937; line-height: 1.3;">' . htmlspecialchars($title) . '</h4>';
			
			// Brand badge (if available)
			if (!empty($product->vendor)) {
				$html .= '<div style="background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 8px; font-size: 10px; font-weight: 600; margin-bottom: 8px; display: inline-block;">' . htmlspecialchars($product->vendor) . '</div>';
			}
			
			// Price
			if ($product->variants->isNotEmpty()) {
				$prices = $product->variants->pluck('price')->filter()->unique()->sort();
				if ($prices->isNotEmpty()) {
					$priceDisplay = $prices->count() === 1 
						? '$' . number_format($prices->first(), 2)
						: 'From $' . number_format($prices->first(), 2);
					$html .= '<div style="background: #059669; color: white; padding: 6px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; margin: 8px 0; display: inline-block;">' . $priceDisplay . '</div>';
				}
			}
			
			// View button
			if ($productUrl) {
				$html .= '<div style="margin-top: 8px;">';
				$html .= '<a href="' . htmlspecialchars($productUrl) . '" target="_blank" style="display: inline-block; background: #3b82f6; color: white; padding: 6px 12px; text-decoration: none; border-radius: 6px; font-size: 11px; font-weight: bold;">🛒 View Product</a>';
				$html .= '</div>';
			}
			
			$html .= '</div>';
		}
		
		$html .= '</div>';
		
		return $html;
	}

}
