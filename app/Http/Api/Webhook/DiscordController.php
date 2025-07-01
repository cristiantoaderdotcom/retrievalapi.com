<?php

namespace App\Http\Api\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use App\Jobs\Reply\Discord;
use App\Models\DiscordBot;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Enums\ConversationRole;

class DiscordController extends Controller {

	private ?DiscordBot $bot = null;

	public function webhook(Request $request): JsonResponse
	{
		// Log raw request details immediately
		Log::channel('discord')->info('Raw Discord webhook received', [
			'headers' => $request->headers->all(),
			'body' => $request->getContent(),
			'method' => $request->method(),
			'route' => $request->route()->getName(),
			'path' => $request->path(),
		]);
		
		// Verify Discord signature
		if (!$this->verifyDiscordSignature($request)) {
			Log::channel('discord')->warning('Invalid Discord signature', [
				'headers' => $request->headers->all()
			]);
			return response()->json(['error' => 'Invalid signature'], 401);
		}
		
		$data = $request->all();
		
		// Discord sends a PING to verify the endpoint initially
		if (isset($data['type']) && $data['type'] === 1) {
			Log::channel('discord')->info('Discord PING received, responding with PONG');
			return response()->json(['type' => 1]);
		}
		
		// Handle interaction
		if (isset($data['type']) && $data['type'] === 2) { // INTERACTION_CREATE
			$this->handleInteraction($data);
			return response()->json(['type' => 5]); // Defer the response, we'll send it later
		}
		
		return response()->json(['success' => true]);
	}

	/**
	 * Verify the Discord signature using Ed25519
	 */
	private function verifyDiscordSignature(Request $request): bool
	{
		$signature = $request->header('X-Signature-Ed25519');
		$timestamp = $request->header('X-Signature-Timestamp');
		$body = $request->getContent();
		
		// Log verification attempt details
		Log::channel('discord')->info('Attempting to verify Discord signature', [
			'has_signature' => !empty($signature),
			'has_timestamp' => !empty($timestamp),
			'has_body' => !empty($body),
			'env' => app()->environment(),
		]);
		
		// In production mode, always verify signatures
		if (app()->environment('production')) {
			// If any required elements are missing, verification fails
			if (empty($signature) || empty($timestamp) || empty($body)) {
				Log::channel('discord')->warning('Missing required signature elements', [
					'has_signature' => !empty($signature),
					'has_timestamp' => !empty($timestamp),
					'has_body' => !empty($body)
				]);
				return false;
			}
			
			// Get Discord app credentials from environment
			$publicKey = config('services.discord.public_key');
				
			if (empty($publicKey)) {
				Log::channel('discord')->warning('Discord public key not set in configuration');
				return false;
			}
			
			// Verify the signature
			$message = $timestamp . $body;
			$publicKeyBin = $this->hexToBin($publicKey);
			$signatureBytes = $this->hexToBin($signature);
			
			try {
				$isValid = sodium_crypto_sign_verify_detached($signatureBytes, $message, $publicKeyBin);
				Log::channel('discord')->info('Signature verification result', [
					'is_valid' => $isValid
				]);
				return $isValid;
			} catch (\Exception $e) {
				Log::channel('discord')->error('Signature verification error', [
					'error' => $e->getMessage()
				]);
				return false;
			}
		} else {
			// For debugging in dev, just log that we'd do verification here
			Log::channel('discord')->info('Bypassing signature verification in non-production environment');
		}
		
		// In non-production environments, optionally bypass verification
		return true;
	}

	/**
	 * Convert hex string to binary
	 */
	private function hexToBin($hex): string
	{
		$len = strlen($hex);
		$binary = '';
		
		for ($i = 0; $i < $len; $i += 2) {
			$binary .= chr(hexdec(substr($hex, $i, 2)));
		}
		
		return $binary;
	}

	/**
	 * Process Discord interaction
	 */
	private function handleInteraction(array $interaction)
	{
		// Check if this is a slash command
		$guildId = $interaction['guild_id'] ?? null;
		
		if (!$guildId) {
			Log::channel('discord')->warning('Missing guild_id in interaction', $interaction);
			return;
		}
		
		// Find the bot configuration by guild ID
		$this->bot = DiscordBot::where('guild_id', $guildId)
			->where('is_active', true)
			->first();
			
		if (!$this->bot) {
			Log::channel('discord')->warning('No bot configuration found for guild', [
				'guild_id' => $guildId
			]);
			return;
		}

		// Log the bot found
		Log::channel('discord')->info('Bot configuration found', [
			'bot_id' => $this->bot->id,
			'workspace_id' => $this->bot->workspace_id,
			'guild_id' => $guildId
		]);
		
		if (isset($interaction['data']['name']) && $interaction['data']['name'] === ltrim($this->bot->command_prefix, '/')) {
			$commandOptions = $interaction['data']['options'] ?? [];
			$question = '';
			
			// Extract the question from options
			foreach ($commandOptions as $option) {
				if ($option['name'] === 'question') {
					$question = $option['value'];
					break;
				}
			}
			
			if (empty($question)) {
				$this->sendHelpResponse($interaction);
				return;
			}
			
			$this->processCommandInteraction($interaction, $question);
		}
	}
	
	/**
	 * Process a slash command interaction
	 */
	private function processCommandInteraction(array $interaction, string $question)
	{
		$userId = $interaction['member']['user']['id'] ?? null;
		$username = $interaction['member']['user']['username'] ?? 'unknown';
		$interactionId = $interaction['id'] ?? null;
		$interactionToken = $interaction['token'] ?? null;
		$guildId = $interaction['guild_id'] ?? null;
		$channelId = $interaction['channel_id'] ?? null;
		
		// Log the command interaction
		Log::channel('discord')->info('Processing slash command', [
			'user_id' => $userId,
			'username' => $username,
			'interaction_id' => $interactionId,
			'guild_id' => $guildId,
			'channel_id' => $channelId,
			'question' => $question
		]);
		
		if (!$interactionId || !$interactionToken) {
			Log::channel('discord')->warning('Invalid interaction data', $interaction);
			return;
		}
		
		// Find or create conversation for this channel/user
		$conversation = Conversation::firstOrCreate(
			[
				'workspace_id' => $this->bot->workspace_id,
				'type' => 'discord',
				'type_source' => $channelId, // Group by Discord channel
			],
			[
				'uuid' => (string) Str::uuid(),
				'source' => 'discord'
			]
		);
		
		// Create message
		$message = ConversationMessage::create([
			'conversation_id' => $conversation->id,
			'role' => ConversationRole::USER,
			'message' => $question,
			'metadata' => [
				'discord' => [
					'interaction_id' => $interactionId,
					'interaction_token' => $interactionToken,
					'user_id' => $userId,
					'username' => $username,
					'guild_id' => $guildId,
					'channel_id' => $channelId
				]
			]
		]);
		
		// Update last_message_at timestamp
		$this->bot->update(['last_message_at' => now()]);
		
		// Get global bot credentials from config
		$applicationId = config('services.discord.application_id');
		$botToken = config('services.discord.token');
		
		// Make a copy of the bot for the job
		$botCopy = clone $this->bot;
		
		// Set application ID and token on the bot copy for the job
		$botCopy->application_id = $applicationId;
		$botCopy->bot_token = $botToken;
		
		// Log the job dispatch
		Log::channel('discord')->info('Dispatching Discord job', [
			'conversation_id' => $conversation->id,
			'message_id' => $message->id,
			'workspace_id' => $this->bot->workspace_id,
			'has_application_id' => !empty($applicationId),
			'has_bot_token' => !empty($botToken)
		]);
		
		// Dispatch the job to the queue instead of handling immediately
		dispatch(new Discord($conversation, $message, $botCopy));
	}
	
	/**
	 * Send a help response when user sends an empty question
	 */
	private function sendHelpResponse(array $interaction)
	{
		$interactionId = $interaction['id'] ?? null;
		$interactionToken = $interaction['token'] ?? null;
		
		if (!$interactionId || !$interactionToken) {
			Log::channel('discord')->warning('Missing interaction token for help response', $interaction);
			return;
		}
		
		$applicationId = config('services.discord.application_id');
		$botToken = config('services.discord.token');
		
		$url = "https://discord.com/api/v10/interactions/{$interactionId}/{$interactionToken}/callback";
		
		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
			'Authorization' => "Bot {$botToken}"
		])->post($url, [
			'type' => 4, // CHANNEL_MESSAGE_WITH_SOURCE
			'data' => [
				'content' => "Please provide a question with the /{$this->bot->command_prefix} command. For example:\n/{$this->bot->command_prefix} What is Laravel?"
			]
		]);
		
		if (!$response->successful()) {
			Log::channel('discord')->error('Failed to send help response', [
				'error' => $response->body(),
				'interaction_id' => $interactionId,
				'interaction_token' => $interactionToken
			]);
		}
	}
}
