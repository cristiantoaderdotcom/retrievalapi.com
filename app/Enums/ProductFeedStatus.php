<?php

namespace App\Enums;

enum ProductFeedStatus: int
{
    case IDLE = 0;
    case PROCESSING = 1;
    case ERROR = 2;

    /**
     * Get the label for the status.
     */
    public function label(): string
    {
        return match($this) {
            self::IDLE => 'Idle',
            self::PROCESSING => 'Processing',
            self::ERROR => 'Error',
        };
    }

    /**
     * Get the color for the status.
     */
    public function color(): string
    {
        return match($this) {
            self::IDLE => 'green',
            self::PROCESSING => 'blue',
            self::ERROR => 'red',
        };
    }

    /**
     * Get all status options as an array.
     */
    public static function options(): array
    {
        return [
            self::IDLE->value => self::IDLE->label(),
            self::PROCESSING->value => self::PROCESSING->label(),
            self::ERROR->value => self::ERROR->label(),
        ];
    }
} 