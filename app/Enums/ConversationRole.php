<?php

namespace App\Enums;

enum ConversationRole: int {
	case USER = 1;
	case ASSISTANT = 2;

	public function label(): string {
		return match ($this) {
			self::USER => 'User',
			self::ASSISTANT => 'Assistant',
		};
	}

	public function isUser(): bool {
		return $this->value === self::USER->value;
	}

	public function isAssistant(): bool {
		return $this->value === self::ASSISTANT->value;
	}
}
