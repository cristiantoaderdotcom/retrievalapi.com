<?php

namespace App\Http\Api\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Jobs\Reply\Facebook;
use App\Models\FacebookPage;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Enums\ConversationRole;

class FacebookController extends Controller {

	public FacebookPage $page;

	public function webhook(Request $request, string $page_uuid): JsonResponse
	{
		// Find the page by UUID
		$this->page = FacebookPage::where('uuid', $page_uuid)
			->where('is_active', true)
			->firstOrFail();

		// i want a log for any request to this webhook
		Log::channel('facebook')->info('Tracked as any request to this webhook', [
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
		Log::channel('facebook')->info('Webhook payload received', [
			'payload' => $request->all(),
			'page_uuid' => $page_uuid,
			'page_id' => $this->page->page_id
		]);
		
		$data = $request->all();
		
		// Process messaging events (direct messages)
		if (isset($data['entry']) && is_array($data['entry'])) {
			foreach ($data['entry'] as $entry) {
			    // Handle messenger messages
				if (isset($entry['messaging']) && is_array($entry['messaging'])) {
					foreach ($entry['messaging'] as $messaging) {
						// Process each message
						if (isset($messaging['message'])) {
							$this->processMessage($messaging);
						}
					}
				}
				
				// Debug: Log entry structure for feed changes
				if (isset($entry['changes'])) {
				    Log::channel('facebook')->info('Feed changes detected', [
				        'entry' => $entry,
				        'changes' => $entry['changes']
				    ]);
				}
				
				// Handle feed/post comments
				if (isset($entry['changes']) && is_array($entry['changes'])) {
				    foreach ($entry['changes'] as $change) {
				        // Debug: Log each change to see its structure
				        Log::channel('facebook')->info('Processing change', [
				            'change' => $change,
				            'field' => $change['field'] ?? 'not set',
				            'value_item' => isset($change['value']) ? ($change['value']['item'] ?? 'not set') : 'value not set',
				            'value_verb' => isset($change['value']) ? ($change['value']['verb'] ?? 'not set') : 'value not set',
				        ]);
				        
				        if ($change['field'] === 'feed' && isset($change['value'])) {
				            // Handle comments - match the actual Facebook webhook format
				            if (isset($change['value']['item']) && $change['value']['item'] === 'comment') {
				                Log::channel('facebook')->info('Comment detected in feed change', [
				                    'value' => $change['value']
				                ]);
				                $this->processComment($change['value']);
				            }
				            // Handle post mentions - this is just in case we need to handle post mentions later
				            elseif (isset($change['value']['item']) && $change['value']['item'] === 'post' && 
				                isset($change['value']['verb']) && $change['value']['verb'] === 'add') {
				                Log::channel('facebook')->info('Post detected in feed change', [
				                    'value' => $change['value']
				                ]);
				                // If you want to process posts specifically, you could call another method here
				            }
				        }
				    }
				}
			}
		}
		
		return response()->json(['success' => true]);
	}


	public function verify(Request $request, string $page_uuid)
	{
		// Find the page by UUID
		$this->page = FacebookPage::where('uuid', $page_uuid)
			->where('is_active', true)
			->firstOrFail();
			
		if ($request->hub_verify_token === $this->page->page_verify_token) {
			return response($request->hub_challenge);
		}
		
		return response('Unauthorized', 403);
	}
	
	/**
	 * Process incoming Facebook message
	 */
	private function processMessage(array $messaging)
	{
		// Skip processing if this page doesn't handle messages
		if (!$this->page->handle_messages) {
			Log::channel('facebook')->info('Messages disabled for this page', [
				'page_id' => $this->page->page_id
			]);
			return;
		}
		
		$senderId = $messaging['sender']['id'] ?? null;
		$timestamp = $messaging['timestamp'] ?? null;
		$messageData = $messaging['message'] ?? [];
		
		// Log the individual message
		Log::channel('facebook')->info('Processing message', [
			'sender_id' => $senderId,
			'page_id' => $this->page->page_id,
			'timestamp' => $timestamp,
			'message' => $messageData,
		]);
		
		if (!$senderId || !isset($messageData['text'])) {
			Log::channel('facebook')->warning('Invalid message data', $messaging);
			return;
		}
		
		// Find or create conversation for this sender
		$conversation = Conversation::firstOrCreate(
			[
				'workspace_id' => $this->page->workspace_id,
				'type' => 'facebook',
				'type_source' => $senderId, // Store the sender's PSID
			],
			[
				'uuid' => (string) Str::uuid(),
				'source' => 'facebook_messenger'
			]
		);
		
		// Create message
		$message = ConversationMessage::create([
			'conversation_id' => $conversation->id,
			'role' => ConversationRole::USER,
			'message' => $messageData['text'],
			'metadata' => [
				'facebook' => [
					'messaging_id' => $messaging['message']['mid'] ?? null,
					'timestamp' => $timestamp
				]
			]
		]);
		
		// Update last_message_at timestamp
		$this->page->update(['last_message_at' => now()]);
		
		// Dispatch the job to the queue instead of handling immediately
		dispatch(new Facebook($conversation, $message, $this->page));
	}
	
	/**
	 * Process incoming Facebook comment
	 */
	private function processComment(array $commentData)
	{
	    // Skip processing if this page doesn't handle comments
	    if (!$this->page->handle_comments) {
	        Log::channel('facebook')->info('Comments disabled for this page', [
	            'page_id' => $this->page->page_id
	        ]);
	        return;
	    }
	    
	    // Extract data from Facebook's actual comment format
	    $commentId = $commentData['comment_id'] ?? null;
	    // The post_id is used here - this is what Facebook sends
	    $postId = $commentData['post_id'] ?? null;
	    $parentId = $commentData['parent_id'] ?? $postId; // Use post_id as fallback
	    $senderId = $commentData['from']['id'] ?? null;
	    $senderName = $commentData['from']['name'] ?? null;
	    $message = $commentData['message'] ?? null;
	    $createdTime = $commentData['created_time'] ?? null;
	    
	    // Log the comment with all available data
	    Log::channel('facebook')->info('Processing comment', [
	        'comment_id' => $commentId,
	        'post_id' => $postId,
	        'parent_id' => $parentId,
	        'sender_id' => $senderId,
	        'sender_name' => $senderName,
	        'message' => $message,
	        'created_time' => $createdTime,
	        'page_id' => $this->page->page_id,
	        'raw_data' => $commentData
	    ]);
	    
	    if (!$commentId || !$senderId || !$message) {
	        Log::channel('facebook')->warning('Invalid comment data', $commentData);
	        return;
	    }
	    
	    // Find or create conversation for this post
	    $conversation = Conversation::firstOrCreate(
	        [
	            'workspace_id' => $this->page->workspace_id,
	            'type' => 'facebook_comment',
	            'type_source' => $postId, // Group by post ID
	        ],
	        [
	            'uuid' => (string) Str::uuid(),
	            'source' => 'facebook_comment'
	        ]
	    );
	    
	    // Create message
	    $message = ConversationMessage::create([
	        'conversation_id' => $conversation->id,
	        'role' => ConversationRole::USER,
	        'message' => $message,
	        'metadata' => [
	            'facebook' => [
	                'comment_id' => $commentId,
	                'post_id' => $postId,
	                'parent_id' => $parentId,
	                'sender_id' => $senderId,
	                'sender_name' => $senderName,
	                'created_time' => $createdTime
	            ]
	        ]
	    ]);
	    
	    // Update last_message_at timestamp
	    $this->page->update(['last_message_at' => now()]);
	    
	    // Dispatch the job to the queue instead of handling immediately
	    dispatch(new Facebook($conversation, $message, $this->page, 'comment'));
	}
}
