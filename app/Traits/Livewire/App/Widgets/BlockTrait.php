<?php

namespace App\Traits\Livewire\App\Widgets;

use App\Models\WidgetBlock;
use Flux\Flux;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

trait BlockTrait {
	public ?string $tab = null;
	public bool $settings = false;

	public bool $visibility = true;
	public bool $status = false;

	public array $audience = [
		'device_type' => 'all',
		'visitor_category' => 'all'
	];

	public array $targeting = [
		'enabled' => false,
		'condition_type' => '',
		'matching_rule' => '',
		'value' => '',
	];

	public array $schedule = [
		'date' => [],
		'destructive' => false,
	];

	#[Locked]
	public WidgetBlock $block;

	#[Locked]
	public $widget;

	public function hydrateBlock(WidgetBlock $block): void {
		$this->block = $block;

		$this->visibility = (bool)$this->block->visibility;
		$this->status = (bool)$this->block->status;

		$this->audience = [
			'device_type' => $this->block->audience_device_type->value ?? null,
			'visitor_category' => $this->block->audience_visitor_category->value ?? null,
		];

		$this->targeting = [
			'enabled' => $this->block->targeting_condition_type && $this->block->targeting_matching_rule && $this->block->targeting_value,
			'condition_type' => $this->block->targeting_condition_type ?? null,
			'matching_rule' => $this->block->targeting_matching_rule ?? null,
			'value' => $this->block->targeting_value ?? null,
		];

		$this->schedule = array_merge($this->schedule, $this->block->setting('schedule'));
	}

	public function getStatus(): bool {
		return $this->status;
	}

	public function getBlock(): WidgetBlock {
		return $this->block;
	}

	#[Renderless]
	public function updatedStatus(): void {
		try {
			$this->validate($this->getValidationRules());
		} catch (ValidationException $e) {
			$this->disable($e->getMessage());
			return;
		}

		$this->block->status = $this->status;

		if($this->status === false) {
			$this->block->visibility = false;
		}

		$this->block->save();

		$this->handleBlockUpdate();
	}

	#[Renderless]
	public function updatedVisibility(): void {
		$this->block->update([
			'visibility' => $this->visibility
		]);

		$this->handleBlockUpdate();
	}

	public function updateVisibility(): void {
		$this->authorize('update', $this->widget);

		$this->block->update([
			'visibility' => !$this->block->visibility,
		]);

		auth()->user()->invalidateWidgetCaches($this->widget->uuid);
		$this->dispatch('refresh');
	}

	protected function getValidationRules(): array {
		return [
			'status' => 'required|boolean',
		];
	}

	protected function disable(?string $message = null): void {
		$this->status = false;
		$this->block->update(['status' => $this->status]);

		if ($message) {
			Flux::toast(variant: 'danger', text: $message);
		}
	}

	private function handleBlockUpdate($forceClose = true): void {
		$this->block->touch();

		if($forceClose) {
			$this->tab = null;
		}

		auth()->user()->invalidateWidgetCaches($this->widget->uuid);
		$this->dispatch('refresh');

		Flux::toast(variant: 'success', text: 'Block updated successfully');
	}

	public function updateScheduleSettings(): void {
		$this->validate([
			'schedule.date' => 'required|array',
		]);

		$this->block->settings()->updateOrCreate(
			['key' => 'schedule'],
			['value' => $this->schedule]
		);

		$this->handleBlockUpdate();
	}

	public function updateAudienceSettings(): void {
		$this->validate([
			'audience.device_type' => 'required',
			'audience.visitor_category' => 'required',
		]);

		$this->block->audience_device_type = data_get($this->audience, 'device_type');
		$this->block->audience_visitor_category = data_get($this->audience, 'visitor_category');

		if($this->targeting['enabled']) {
			$this->block->targeting_condition_type = data_get($this->targeting, 'condition_type');
			$this->block->targeting_matching_rule = data_get($this->targeting, 'matching_rule');
			$this->block->targeting_value = data_get($this->targeting, 'value');
		} else {
			$this->block->targeting_condition_type = null;
			$this->block->targeting_matching_rule = null;
			$this->block->targeting_value = null;
		}

		$this->block->save();

		$this->handleBlockUpdate();
	}
}
