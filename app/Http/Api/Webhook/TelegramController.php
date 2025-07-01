<?php

namespace App\Http\Api\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use App\Jobs\Reply\Telegram;
use App\Models\TelegramBot;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Enums\ConversationRole;

class TelegramController extends Controller {

	public TelegramBot $bot;

	public function webhook(Request $request, string $bot_uuid): JsonResponse
	{
		// Find the bot by UUID
		$this->bot = TelegramBot::where('uuid', $bot_uuid)
			->where('is_active', true)
			->firstOrFail();

		// Log the entire webhook payload
		Log::channel('telegram')->info('Webhook payload received', [
			'payload' => $request->all(),
			'bot_uuid' => $bot_uuid,
			'bot_username' => $this->bot->bot_username
		]);
		
		$data = $request->all();
		
		// Process message if it exists
		if (isset($data['message'])) {
			$this->processMessage($data['message']);
		}
		
		return response()->json(['success' => true]);
	}

	/**
	 * Process incoming Telegram message
	 */
	private function processMessage(array $message)
	{
		$chatId = $message['chat']['id'] ?? null;
		$text = $message['text'] ?? null;
		$fromId = $message['from']['id'] ?? null;
		$fromUsername = $message['from']['username'] ?? null;
		$messageId = $message['message_id'] ?? null;
		
		// Log the individual message
		Log::channel('telegram')->info('Processing message', [
			'chat_id' => $chatId,
			'from_id' => $fromId,
			'from_username' => $fromUsername,
			'text' => $text,
			'message_id' => $messageId,
		]);
		
		if (!$chatId || !$text) {
			Log::channel('telegram')->warning('Invalid message data', $message);
			return;
		}
		
		// Check if the message starts with the command prefix
		if (!Str::startsWith($text, $this->bot->command_prefix)) {
			Log::channel('telegram')->info('Ignoring message without command prefix', [
				'prefix' => $this->bot->command_prefix,
				'message' => $text
			]);
			return;
		}
		
		// Extract the actual query by removing the command prefix
		$query = trim(Str::substr($text, Str::length($this->bot->command_prefix)));
		
		// If query is empty, send a helpful message
		if (empty($query)) {
			$this->sendHelpMessage($chatId);
			return;
		}
		
		// Find or create conversation for this chat
		$conversation = Conversation::firstOrCreate(
			[
				'workspace_id' => $this->bot->workspace_id,
				'type' => 'telegram',
				'type_source' => (string) $chatId,
			],
			[
				'uuid' => (string) Str::uuid(),
				'source' => 'telegram'
			]
		);
		
		// Create message
		$message = ConversationMessage::create([
			'conversation_id' => $conversation->id,
			'role' => ConversationRole::USER,
			'message' => $query,
			'metadata' => [
				'telegram' => [
					'message_id' => $messageId,
					'from_id' => $fromId,
					'from_username' => $fromUsername
				]
			]
		]);
		
		// Update last_message_at timestamp
		$this->bot->update(['last_message_at' => now()]);
		
		// Dispatch the job to the queue instead of handling immediately
		dispatch(new Telegram($conversation, $message, $this->bot));
	}
	
	/**
	 * Send a help message when user sends just the command without a query
	 */
	private function sendHelpMessage(string $chatId)
	{
		$url = "https://api.telegram.org/bot{$this->bot->bot_token}/sendMessage";
		
		$helpMessage = "Please provide a question after the {$this->bot->command_prefix} command. For example:\n\n{$this->bot->command_prefix} What is Laravel?";
		
		$response = Http::post($url, [
			'chat_id' => $chatId,
			'text' => $helpMessage
		]);
		
		if (!$response->successful()) {
			Log::channel('telegram')->error('Failed to send help message', [
				'error' => $response->body(),
				'chat_id' => $chatId
			]);
		}
	}
}
