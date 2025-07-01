<?php

namespace App\Http\Api\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Jobs\Reply\Instagram;
use App\Models\InstagramPage;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Enums\ConversationRole;

class InstagramController extends Controller {

	public InstagramPage $page;

	public function webhook(Request $request, string $page_uuid): JsonResponse
	{
		// Test log to verify webhook is being called
		Log::channel('instagram')->info('Instagram webhook received', [
			'uri' => $request->getRequestUri(),
			'method' => $request->getMethod(),
			'ip' => $request->ip(),
			'page_uuid' => $page_uuid
		]);

		// Find the page by UUID
		$this->page = InstagramPage::where('uuid', $page_uuid)
			->where('is_active', true)
			->firstOrFail();

		// Log any request to this webhook
		Log::channel('instagram')->info('Tracked as any request to this webhook', [
			'payload' => $request->all(),
			'page_uuid' => $page_uuid,
			'page_id' => $this->page->page_id
		]);
		
		// Handle verification request
		if ($request->has('hub.challenge')) {
			$request->validate([
				'hub.challenge' => 'required',
				'hub.verify_token' => 'required',
			]);

			if ($request->input('hub.verify_token') === $this->page->page_verify_token) {
				return response()->json(['hub.challenge' => $request->input('hub.challenge')]);
			}

			return response()->json(['error' => 'Invalid verify token'], 400);
		}
		
		// Handle incoming webhook event
		// Log the entire webhook payload
		Log::channel('instagram')->info('Webhook payload received', [
			'payload' => $request->all(),
			'page_uuid' => $page_uuid,
			'page_id' => $this->page->page_id
		]);
		
		$data = $request->all();
		
		// First, check if we have a direct message format (messaging field)
		if (isset($data['entry']) && is_array($data['entry'])) {
			foreach ($data['entry'] as $entry) {
				// Handle standard messaging format
				if (isset($entry['messaging']) && is_array($entry['messaging'])) {
					foreach ($entry['messaging'] as $messaging) {
						// Process each message
						if (isset($messaging['message'])) {
							$this->processMessage($messaging);
						}
					}
				}
				
				// Handle newer Instagram webhook format (changes field)
				if (isset($entry['changes']) && is_array($entry['changes'])) {
					foreach ($entry['changes'] as $change) {
						// Enhanced debugging for change structure
						Log::channel('instagram')->info('Processing Instagram change', [
							'change' => $change,
							'field' => $change['field'] ?? 'not set',
							'value' => $change['value'] ?? 'no value'
						]);
						
						// Instagram comments handling
						if (($change['field'] === 'comments' || $change['field'] === 'mentions') && isset($change['value'])) {
							Log::channel('instagram')->info('Comment or mention detected in Instagram', [
								'value' => $change['value']
							]);
							$this->processComment($change['value']);
						}
						
						// Direct messages handling
						if ($change['field'] === 'messages' && isset($change['value'])) {
							Log::channel('instagram')->info('Direct message detected in Instagram', [
								'value' => $change['value']
							]);
							$this->processDirectMessage($change['value']);
						}
					}
				}
				
				// Fallback for direct object values
				if (isset($entry['id']) && isset($entry['time'])) {
					// This could be a direct message or comment not in the expected format
					if (isset($entry['messaging']) && is_array($entry['messaging']) && !empty($entry['messaging'])) {
						$this->processMessage($entry['messaging'][0]);
					} else if (isset($entry['changes']) && is_array($entry['changes']) && !empty($entry['changes'])) {
						$change = $entry['changes'][0];
						if (isset($change['value'])) {
							if ($change['field'] === 'messages') {
								$this->processDirectMessage($change['value']);
							} else if ($change['field'] === 'comments' || $change['field'] === 'mentions') {
								$this->processComment($change['value']);
							}
						}
					}
				}
			}
		} else {
			// Handle potential direct webhook payloads (not wrapped in entry)
			if (isset($data['object']) && $data['object'] === 'instagram') {
				Log::channel('instagram')->info('Direct Instagram webhook object received', [
					'data' => $data
				]);
				
				if (isset($data['message'])) {
					$this->processDirectMessage($data);
				} else if (isset($data['comment_id'])) {
					$this->processComment($data);
				}
			}
		}
		
		return response()->json(['success' => true]);
	}


	public function verify(Request $request, string $page_uuid)
	{
		// Find the page by UUID
		$this->page = InstagramPage::where('uuid', $page_uuid)
			->where('is_active', true)
			->firstOrFail();
			
		if ($request->hub_verify_token === $this->page->page_verify_token) {
			return response($request->hub_challenge);
		}
		
		return response('Unauthorized', 403);
	}
	
	/**
	 * Process incoming standard message (legacy format)
	 */
	private function processMessage(array $messaging)
	{
		// Skip processing if this page doesn't handle messages
		if (!$this->page->handle_messages) {
			Log::channel('instagram')->info('Messages disabled for this page', [
				'page_id' => $this->page->page_id
			]);
			return;
		}
		
		$senderId = $messaging['sender']['id'] ?? null;
		$timestamp = $messaging['timestamp'] ?? null;
		$messageData = $messaging['message'] ?? [];
		$messageId = $messageData['mid'] ?? null;
		
		// Check for duplicate message processing
		if ($messageId) {
			$existingMessage = ConversationMessage::where('metadata->instagram->message_id', $messageId)->first();
			if ($existingMessage) {
				Log::channel('instagram')->info('Skipping duplicate message', [
					'message_id' => $messageId,
					'existing_conversation_id' => $existingMessage->conversation_id
				]);
				return;
			}
		}
		
		// Skip if this is our own page/account sending the message
		if ($senderId == $this->page->page_id) {
			Log::channel('instagram')->info('Skipping our own message', [
				'page_id' => $this->page->page_id,
				'sender_id' => $senderId
			]);
			return;
		}
		
		// Check for is_echo flag (indicates a message sent by the page/bot)
		if (isset($messageData['is_echo']) && $messageData['is_echo'] === true) {
			Log::channel('instagram')->info('Skipping echo message', [
				'page_id' => $this->page->page_id
			]);
			return;
		}
		
		// Log the individual message
		Log::channel('instagram')->info('Processing message', [
			'sender_id' => $senderId,
			'page_id' => $this->page->page_id,
			'timestamp' => $timestamp,
			'message' => $messageData,
		]);
		
		if (!$senderId || !isset($messageData['text'])) {
			Log::channel('instagram')->warning('Invalid message data', $messaging);
			return;
		}
		
		// Find or create conversation for this sender
		$conversation = Conversation::firstOrCreate(
			[
				'workspace_id' => $this->page->workspace_id,
				'type' => 'instagram',
				'type_source' => $senderId, // Store the sender's PSID
			],
			[
				'uuid' => (string) Str::uuid(),
				'source' => 'instagram_direct'
			]
		);
		
		// Create message
		$message = ConversationMessage::create([
			'conversation_id' => $conversation->id,
			'role' => ConversationRole::USER,
			'message' => $messageData['text'],
			'metadata' => [
				'instagram' => [
					'message_id' => $messageId, // Store message ID consistently
					'timestamp' => $timestamp
				]
			]
		]);
		
		// Update last_message_at timestamp
		$this->page->update(['last_message_at' => now()]);
		
		// Dispatch the job to the queue instead of handling immediately
		dispatch(new Instagram($conversation, $message, $this->page));
	}
	
	/**
	 * Process Instagram direct message (new format)
	 */
	private function processDirectMessage(array $messageData)
	{
	    // Skip processing if this page doesn't handle messages
	    if (!$this->page->handle_messages) {
	        Log::channel('instagram')->info('Messages disabled for this page', [
	            'page_id' => $this->page->page_id
	        ]);
	        return;
	    }
	    
	    // Extract data from Instagram's message format - check for all possible locations
	    $messageId = $messageData['id'] ?? ($messageData['message']['mid'] ?? null);
	    
	    // Skip if we don't have a message ID to track duplicates
	    if (!$messageId) {
	        Log::channel('instagram')->warning('Message missing ID, cannot check for duplicates', [
	            'data' => $messageData
	        ]);
	    } else {
	        // Check for duplicate message processing using message ID
	        $existingMessage = ConversationMessage::where('metadata->instagram->message_id', $messageId)->first();
	        if ($existingMessage) {
	            Log::channel('instagram')->info('Skipping duplicate message', [
	                'message_id' => $messageId,
	                'existing_conversation_id' => $existingMessage->conversation_id
	            ]);
	            return;
	        }
	    }
	    
	    // Instagram has different formats for sender based on the webhook type
	    $senderId = $messageData['from']['id'] ?? $messageData['sender']['id'] ?? null;
	    
	    // Skip if this is our own page/account sending the message (prevents echo)
	    if ($senderId == $this->page->page_id) {
	        Log::channel('instagram')->info('Skipping our own message', [
	            'page_id' => $this->page->page_id,
	            'sender_id' => $senderId
	        ]);
	        return;
	    }
	    
	    // Check for is_echo flag which indicates a message sent by the page/bot
	    if (isset($messageData['is_echo']) && $messageData['is_echo'] === true) {
	        Log::channel('instagram')->info('Skipping echo message', [
	            'page_id' => $this->page->page_id,
	            'message_id' => $messageId
	        ]);
	        return;
	    }
	    
	    // The message can be in different places based on webhook structure
	    $message = $messageData['message'] ?? $messageData['text'] ?? null;
	    if (is_array($message) && isset($message['text'])) {
	        $message = $message['text'];
	    }
	    
	    $timestamp = $messageData['created_time'] ?? $messageData['timestamp'] ?? now()->timestamp;
	    
	    // Log the message with all available data
	    Log::channel('instagram')->info('Processing direct message details', [
	        'message_id' => $messageId,
	        'sender_id' => $senderId,
	        'message' => $message,
	        'timestamp' => $timestamp,
	        'page_id' => $this->page->page_id,
	        'raw_data' => $messageData
	    ]);
	    
	    if (!$senderId || !$message) {
	        Log::channel('instagram')->warning('Invalid direct message data', $messageData);
	        return;
	    }
	    
	    // Find or create conversation for this sender
	    $conversation = Conversation::firstOrCreate(
	        [
	            'workspace_id' => $this->page->workspace_id,
	            'type' => 'instagram',
	            'type_source' => $senderId,
	        ],
	        [
	            'uuid' => (string) Str::uuid(),
	            'source' => 'instagram_direct'
	        ]
	    );
	    
	    // Create message
	    $message = ConversationMessage::create([
	        'conversation_id' => $conversation->id,
	        'role' => ConversationRole::USER,
	        'message' => $message,
	        'metadata' => [
	            'instagram' => [
	                'message_id' => $messageId,
	                'sender_id' => $senderId,
	                'timestamp' => $timestamp
	            ]
	        ]
	    ]);
	    
	    // Update last_message_at timestamp
	    $this->page->update(['last_message_at' => now()]);
	    
	    try {
	        // Dispatch the job to the queue
	        dispatch(new Instagram($conversation, $message, $this->page));
	        Log::channel('instagram')->info('Successfully dispatched Instagram reply job', [
	            'conversation_id' => $conversation->id,
	            'message_id' => $message->id
	        ]);
	    } catch (\Exception $e) {
	        Log::channel('instagram')->error('Failed to dispatch Instagram reply job', [
	            'error' => $e->getMessage(),
	            'trace' => $e->getTraceAsString()
	        ]);
	    }
	}
	
	/**
	 * Process Instagram comment
	 */
	private function processComment(array $commentData)
	{
	    // Skip processing if this page doesn't handle comments
	    if (!$this->page->handle_comments) {
	        Log::channel('instagram')->info('Comments disabled for this page', [
	            'page_id' => $this->page->page_id
	        ]);
	        return;
	    }
	    
	    // Extract data - Instagram comment format can differ from Facebook
	    $commentId = $commentData['id'] ?? $commentData['comment_id'] ?? null;
	    $mediaId = $commentData['media']['id'] ?? $commentData['media_id'] ?? null;
	    $senderId = $commentData['from']['id'] ?? $commentData['sender_id'] ?? null;
	    
	    // Skip if this is our own page/account commenting
	    if ($senderId == $this->page->page_id) {
	        Log::channel('instagram')->info('Skipping our own comment', [
	            'page_id' => $this->page->page_id,
	            'comment_id' => $commentId
	        ]);
	        return;
	    }
	    
	    // Check for duplicate comment processing
	    if ($commentId) {
	        $existingMessage = ConversationMessage::where('metadata->instagram->comment_id', $commentId)->first();
	        if ($existingMessage) {
	            Log::channel('instagram')->info('Skipping duplicate comment', [
	                'comment_id' => $commentId,
	                'existing_conversation_id' => $existingMessage->conversation_id
	            ]);
	            return;
	        }
	    }
	    
	    // Log the comment with all available data
	    Log::channel('instagram')->info('Processing comment', [
	        'comment_id' => $commentId,
	        'media_id' => $mediaId,
	        'sender_id' => $senderId,
	        'page_id' => $this->page->page_id,
	        'raw_data' => $commentData
	    ]);
	    
	    if (!$commentId || !$senderId || !$mediaId) {
	        Log::channel('instagram')->warning('Invalid comment data', $commentData);
	        return;
	    }
	    
	    // Find or create conversation for this media
	    $conversation = Conversation::firstOrCreate(
	        [
	            'workspace_id' => $this->page->workspace_id,
	            'type' => 'instagram_comment',
	            'type_source' => $mediaId, // Group by media ID
	        ],
	        [
	            'uuid' => (string) Str::uuid(),
	            'source' => 'instagram_comment'
	        ]
	    );
	    
	    // Create message
	    $message = ConversationMessage::create([
	        'conversation_id' => $conversation->id,
	        'role' => ConversationRole::USER,
	        'message' => $commentData['text'] ?? null,
	        'metadata' => [
	            'instagram' => [
	                'comment_id' => $commentId,
	                'media_id' => $mediaId,
	                'sender_id' => $senderId
	            ]
	        ]
	    ]);
	    
	    // Update last_message_at timestamp
	    $this->page->update(['last_message_at' => now()]);
	    
	    // Dispatch the job to the queue
	    dispatch(new Instagram($conversation, $message, $this->page, 'comment'));
	}
}
