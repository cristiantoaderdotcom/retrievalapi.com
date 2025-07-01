<?php

namespace App\Enums;

enum ResourceStatus: int {
	case PROCESSED = 1;
	case PROCESSING = 2;
	case FAILED = 3;
	case HIDDEN = 4;

	/**
	 * Get the metadata for all statuses.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function metadata(): array {
		return [
			self::PROCESSED->value => [
				'label' => 'Processed',
				'color' => 'lime',
				'icon' => 'check-circle',
			],
			self::PROCESSING->value => [
				'label' => 'Processing',
				'color' => 'amber',
				'icon' => 'clock',
			],
			self::FAILED->value => [
				'label' => 'Failed',
				'color' => 'red',
				'icon' => 'exclamation-circle',
			],
			self::HIDDEN->value => [
				'label' => 'Hidden',
				'color' => 'zinc',
				'icon' => 'eye-slash',
			],
		];
	}

	/**
	 * Get the label for the enum instance.
	 *
	 * @return string
	 */
	public function label(): string {
		return self::metadata()[$this->value]['label'];
	}

	/**
	 * Get the color for the enum instance.
	 *
	 * @return string
	 */
	public function color(): string {
		return self::metadata()[$this->value]['color'];
	}

	/**
	 * Get the icon for the enum instance.
	 *
	 * @return string|null
	 */
	public function icon(): ?string {
		return self::metadata()[$this->value]['icon'];
	}

	/**
	 * Get all statuses as an array.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function toArray(): array {
		return array_map(fn($status) => [
			'label' => $status['label'],
			'value' => array_search($status, self::metadata()),
			'color' => $status['color'],
			'icon' => $status['icon'],
		], self::metadata());
	}

	public function canHide(): bool {
		return $this->value !== self::HIDDEN->value && $this->value !== self::PROCESSED->value;
	}

	public function isProcessing(): bool {
		return $this->value === self::PROCESSING->value;
	}

	public function isFailed(): bool {
		return $this->value === self::FAILED->value;
	}
}
