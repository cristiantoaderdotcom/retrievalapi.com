<?php

namespace App\Traits\Livewire\App\Widgets;

use Flux\Flux;
use Livewire\Attributes\Renderless;

trait HasButton {
	public array $button = [
		'type' => '',
		'href' => '',
		'target' => '',
		'icon' => '',
		'text' => '',
		'align' => 'pw:justify-start',
		'background' => [
			'type' => 'solid',
			'solid' => [
				'color' => '#070707'
			],
			'gradient' => [
				'direction' => 'to right',
				'from' => '#070707',
				'via' => '',
				'to' => '#070707'
			]
		],
		'color' => '#f4f4f5',
		'animation' => 'none',
	];

	public function hydrateButton(): void {
		$this->button = array_merge(
			$this->button,
			$this->block->setting('button'),
			[
				'type' => $this->getButtonType()
			]
		);
	}

	protected function getButtonType(): string {
		return 'button';
	}

	#[Renderless]
	public function updatedButtonText(): void {
		$this->button['type'] = $this->getButtonType();

		$this->block->settings()->updateOrCreate(
			['key' => 'button'],
			['value' => $this->button]
		);

		$this->handleBlockUpdate();
	}

	#[Renderless]
	public function updatedButtonHref(): void {
		try {
			$this->validate([
				'button.href' => 'required|string',
			], [
				'button.href.required' => 'The button link field is required',
			]);
		} catch (\Illuminate\Validation\ValidationException $e) {
			$this->disable($e->getMessage());
			return;
		}

		$this->button['type'] = $this->getButtonType();

		$this->block->settings()->updateOrCreate(
			['key' => 'button'],
			['value' => $this->button]
		);

		$this->handleBlockUpdate();
	}

	#[Renderless]
	public function updateAnimationSettings(): void {
		$this->validate([
			'button.animation' => 'required|string',
		], [
			'button.animation.required' => 'The animation field is required',
		]);

		if(!auth()->user()->hasFeatureAccess('animation_block_settings')) {
			abort(403);
		}

		$this->block->settings()->updateOrCreate(
			['key' => 'button'],
			['value' => $this->button]
		);

		$this->handleBlockUpdate();
	}

	public function delete(string $settingKey): void {
		$this->deleteIcon($settingKey);

		$this->block->settings()->updateOrCreate(
			['key' => 'button'],
			['value' => $this->button]
		);

		$this->handleBlockUpdate();
	}
}
