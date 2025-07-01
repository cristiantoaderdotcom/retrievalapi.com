<?php

namespace App\Http\Api\Controllers\Response;

use App\Enums\ConversationRole;
use App\Http\Controllers\Controller;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Workspace;
use App\Models\Lead;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Email;
use Symfony\Component\HttpFoundation\Response;
use App\Jobs\Reply\Website;
class WebsiteController extends Controller {
	public function session(Request $request): JsonResponse {
		$workspace = Workspace::query()
			->where('uuid', $request->input("workspace_uuid"))
			->firstOrFail();

			
			
		// if($workspace->loads >= 20000 && !$workspace->user->pro) {
		// 	return response()->json([
		// 		'status' => 'error',
		// 		'error' => 'load_limit_reached',
		// 		'message' => 'This chatbot has reached its limit of 20,000 loads. Please upgrade to a paid plan to continue.'
		// 	]);
		// }

		// $workspace->increment('loads');	

		$conversation = Conversation::query()
			->where('uuid', $request->input("conversation_uuid"))
			->where('workspace_id', $workspace->id)
			->first();

		if($conversation) {
			$messages = $conversation->messages()
				->orderBy('created_at')
				->get();

			$messages->transform(function ($message) {
				return [
					'id' => $message->id,
					'role_label' => $message->role->label(),
					'message' => $message->message,
					'created_at' => $message->created_at,
					'disliked' => $message->disliked,
				];
			});
		}

		return response()->json([
			'status' => 'success',
			'conversation_uuid' => $conversation->uuid ?? Str::uuid(),
			'messages' => $messages ?? [],
		]);
	}

	public function message(Request $request): JsonResponse {
		$request->validate([
			'workspace_uuid' => 'required|uuid',
			'conversation_uuid' => 'nullable|string',
			'message' => 'required|string',
			'user_time' => 'nullable|array',
		]);

		$workspace = Workspace::query()
			->where('uuid', $request->input("workspace_uuid"))
			->firstOrFail();

		$conversation = Conversation::query()
			->where('uuid', $request->input('conversation_uuid'))
			->first();

		if(!$conversation) {
			$conversation = Conversation::query()
				->create([
					'uuid' => $request->input("conversation_uuid") ?? Str::uuid(),
					'workspace_id' => $workspace->id,
					'ip_address' => $request->ip(),
					'user_agent' => $request->header('User-Agent'),
					'source' => $request->header('Referer'),
					'query_string' => $request->getQueryString(),
				]);
		}

		if (!$conversation) {
			return response()->json([
				'status' => 'error',
				'message' => 'Conversation not found'
			], 404);
		}


		$message = ConversationMessage::query()
			->create([
				'conversation_id' => $conversation->id,
				'role' => ConversationRole::USER,
				'message' => $request->message,
				'metadata' => [
					'user_time' => $request->input('user_time'),
				],
				'total_tokens' => 0
			]);
			
		// // Check if Calendly integration should be triggered BEFORE generating AI response
		// $shouldAddCalendly = false;
		// $calendlyData = null;
		// $calendlySettings = data_get($workspace->setting, 'calendly', []);

		// if (!empty($calendlySettings['enabled']) && !empty($calendlySettings['url'])) {
		// 	// Get trigger keywords from settings or use defaults if not set
		// 	$triggerKeywordsString = data_get($calendlySettings, 'trigger_keywords', 'schedule,meeting,appointment,book,calendar,time,availability');
		// 	$triggerKeywords = array_map('trim', explode(',', $triggerKeywordsString));
			
		// 	// Convert message to lowercase for case-insensitive matching
		// 	$userMessage = strtolower($message->message);
			
		// 	// Check if the user message contains any of the trigger keywords
		// 	foreach ($triggerKeywords as $keyword) {
		// 		if (!empty($keyword) && strpos($userMessage, $keyword) !== false) {
		// 			$shouldAddCalendly = true;
		// 			$calendlyData = [
		// 				'url' => $calendlySettings['url'] ?? '',
		// 				'message' => $calendlySettings['message'] ?? 'You can easily schedule a meeting with me using my calendar. Just click the button below:',
		// 				'button_text' => $calendlySettings['button_text'] ?? 'Schedule a meeting'
		// 			];
		// 			break;
		// 		}
		// 	}
		// }

		// // If Calendly is triggered by the user's message, return the Calendly data directly without AI response
		// if ($shouldAddCalendly) {
		// 	return response()->json([
		// 		'status' => 'success',
		// 		'conversation_uuid' => $conversation->uuid,
		// 		'calendly' => $calendlyData,
		// 		'direct_calendly' => true,
		// 	]);
		// }

	

		// Process AI response (no streaming)
		$assistant = Website::dispatchSync($conversation, $message);

		// $assistantMessage = ConversationMessage::query()
		// 	->create([
		// 		'conversation_id' => $conversation->id,
		// 		'role' => ConversationRole::ASSISTANT,
		// 		'message' => 'Hello, how can I help you today?',
		// 		'metadata' => [],
		// 		'total_tokens' => 0
		// 	]);

		// // Check if AI response should trigger Calendly
		// if (!$shouldAddCalendly && !empty($calendlySettings['enabled']) && !empty($calendlySettings['url'])) {
		// 	// Convert assistant message to lowercase for case-insensitive matching
		// 	$assistantMessage = strtolower($assistant->message);
			
		// 	// Check if the AI response contains any of the trigger keywords
		// 	foreach ($triggerKeywords as $keyword) {
		// 		if (!empty($keyword) && strpos($assistantMessage, $keyword) !== false) {
		// 			$shouldAddCalendly = true;
		// 			$calendlyData = [
		// 				'url' => $calendlySettings['url'] ?? '',
		// 				'message' => $calendlySettings['message'] ?? 'You can easily schedule a meeting with me using my calendar. Just click the button below:',
		// 				'button_text' => $calendlySettings['button_text'] ?? 'Schedule a meeting'
		// 			];
		// 			break;
		// 		}
		// 	}
		// }

		return response()->json([
			'status' => 'success',
			'conversation_uuid' => $conversation->uuid,
			'message' => $assistant,
			// 'calendly' => $shouldAddCalendly ? $calendlyData : null,
		]);
	}

	public function dislikeMessage(Request $request): JsonResponse {
		try {
			$request->validate([
				'workspace_uuid' => 'required|uuid',
				'conversation_uuid' => 'required|uuid',
				'message_id' => 'required|integer',
			]);

			// First, verify the conversation belongs to this chatbot and the visitor
			$conversation = Conversation::query()
				->where('uuid', $request->input('conversation_uuid'))
				->where('workspace_id', function ($query) use ($request) {
					$query->select('id')
						->from('workspaces')
						->where('uuid', $request->input('workspace_uuid'))
						->limit(1);
				})
				->firstOrFail();

			// Find the message and verify it belongs to this conversation
			$message = ConversationMessage::query()
				->where('id', $request->input('message_id'))
				->where('conversation_id', $conversation->id)
				->where('role', ConversationRole::ASSISTANT) // Only assistant messages can be disliked
				->firstOrFail();

			// Update the disliked flag
			$message->update(['disliked' => true]);

			return response()->json([
				'status' => 'success',
				'message' => 'Feedback received'
			]);

		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return response()->json([
				'status' => 'error',
				'message' => 'Message not found or not authorized to dislike'
			], 404);
		} catch (\Illuminate\Validation\ValidationException $e) {
			return response()->json([
				'status' => 'error',
				'message' => 'Validation failed',
				'errors' => $e->errors()
			], 422);
		} catch (Exception $e) {
			Log::error(__CLASS__, [
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);

			return response()->json([
				'status' => 'error',
				'message' => 'An unexpected error occurred. Please try again later.'
			], 500);
		}
	}

	public function lead(Request $request): JsonResponse {
		try {
			$request->validate([
				'workspace_uuid' => 'required|uuid',
				'conversation_uuid' => 'required|uuid',
				'name' => 'required|string',
				'email' => 'required|email:rfc,dns',
			]);

			$conversation = Conversation::query()
				->where('uuid', $request->input('conversation_uuid'))
				->firstOrFail();

			$workspace = Workspace::query()
				->where('uuid', $request->input('workspace_uuid'))
				->firstOrFail();

			Lead::query()->create([
				'conversation_id' => $conversation->id,
				'workspace_id' => $workspace->id,
				'name' => $request->input('name'),
				'email' => $request->input('email'),
				'phone' => $request->input('phone') ?? null,
				'source_type' => 'website',
				'ip_address' => $request->ip(),
			]);

			return response()->json([
				'status' => 'success',
				'message' => 'Lead created successfully'
			]);

		} catch (\Illuminate\Validation\ValidationException $e) {
			return response()->json([
				'status' => 'error',
				'message' => 'Validation failed',
				'errors' => $e->errors()
			], 422);
		} catch (Exception $e) {
			Log::error(__CLASS__, [
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);

			return response()->json([
				'status' => 'error',
				'message' => 'An unexpected error occurred. Please try again later.'
			], 500);
		}
	}
}
