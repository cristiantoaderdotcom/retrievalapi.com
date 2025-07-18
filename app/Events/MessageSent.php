<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow {
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public Message $message;

	/**
	 * Create a new event instance.
	 */
	public function __construct(Message $message) {
		$this->message = $message;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return array<int, \Illuminate\Broadcasting\Channel>
	 */
	public function broadcastOn(): array {
		return [
			new Channel("chat-{$this->message->session}"),
		];
	}

	public function broadcastWith(): array {
		return [
			'message' => $this->message,
			'time' => now()->toISOString()
		];
	}
}