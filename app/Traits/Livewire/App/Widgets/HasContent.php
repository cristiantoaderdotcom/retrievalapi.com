<?php

namespace App\Traits\Livewire\App\Widgets;

use Livewire\Attributes\Renderless;

trait HasContent {
	public array $content = [
		'heading' => [
			'text' => '',
			'size' => 'pw:text-sm',
			'weight' => 'pw:font-normal'
		],
		'subheading' => [
			'text' => '',
			'size' => 'pw:text-sm',
			'weight' => 'pw:font-normal'
		],
	];

	public function hydrateContent(): void {
		$this->content = array_merge(
			$this->content,
			$this->block->setting('content')
		);
	}

	#[Renderless]
	public function updatedContentHeadingText(): void {
		try {
			$this->validate([
				'content.heading.text' => 'required|string',
			], [
				'content.heading.text.required' => 'The text field is required',
			]);
		} catch (\Illuminate\Validation\ValidationException $e) {
			$this->disable($e->getMessage());
			return;
		}

		$this->block->settings()->updateOrCreate(
			['key' => 'content'],
			['value' => $this->content]
		);

		$this->dispatch('refresh');
	}
}
