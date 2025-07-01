<?php

namespace App\Traits\Livewire\App\KnowledgeBase;

use App\Enums\ResourceStatus;
use App\Jobs\TrainSource;
use App\Models\KnowledgeBaseFileResource;
use App\Models\KnowledgeBaseResource;
use App\Models\Workspace;
use Flux\Flux;	
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait HasResources {
	public array $filters = [
		'match' => 'contains',
		'search' => '',
		'status' => '',
		'visibility' => '',
	];

	public array $statuses = [];
	public array $selected = [];

	public int $processing = 0;
	public int $processed = 0;
	public int $failed = 0;

	public function process(): void {
		if(auth()->user()->ai_context_limit === 0) {
			Flux::toast(variant: 'danger', text: 'You have no AI context limit left.');
			Flux::modal('recharge-tokens')->show();
			$this->selected = [];
			return;
		}

		$charactersCount = KnowledgeBaseResource::query()
			->where('workspace_id', $this->workspace->id)
			->sum('characters_count');

		if($charactersCount > 500 * 1000) {
			Flux::toast(variant: 'danger', text: 'You have reached the maximum limit of 500K characters AI context limit for this workspace.');
			return;
		}

		$keys = collect($this->selected)
			->filter()
			->keys()
			->all();

		$this->selected = [];
		$this->polling = true;

		foreach ($keys as $key) {
			$resource = KnowledgeBaseResource::query()
				->where('id', $key)
				->firstOrFail();

			$resource->update([
				'status' => ResourceStatus::PROCESSING,
				'process_started_at' => now(),
				'process_completed_at' => null,
			]);

			TrainSource::dispatch($resource);
		}
	}

	public function delete($id): void {
		

		$keys = collect($this->selected)
			->filter()
			->keys()
			->all();

		$this->selected = [];

		foreach ($keys as $key) {
			$resource = KnowledgeBaseResource::query()
				->where('id', $key)
				->with('resourceable')
				->firstOrFail();
			
			if ($resource->resourceable instanceof KnowledgeBaseFileResource) {
				Log::info('Deleting file resource', ['resource' => $resource->resourceable->path]);
				Storage::disk('public')->delete($resource->resourceable->path);
			}
			

			$resource->resourceable->delete();
			$resource->conversations()->delete();
			$resource->delete();
		}

		Flux::toast(variant: 'success', text: 'The link has been successfully deleted.');
	}

	public function hide($id): void {
	

		$keys = collect($this->selected)
			->filter()
			->keys()
			->all();

		$this->selected = [];

		foreach ($keys as $key) {
			$resource = KnowledgeBaseResource::query()
				->where('id', $key)
				->firstOrFail();

			$resource->update([
				'status' => ResourceStatus::HIDDEN,
			]);
		}
	}


	public function filter(): void {
		$this->resetPage();
	}

	public function resetFilters(): void {
		$this->filters = collect($this->filters)
			->map(function ($value, $key) {
				return $key === 'match' ? $value : '';
			})
			->toArray();

		$this->resetPage();
	}

	public function getFilterExistsProperty(): bool {
		return collect($this->filters)
			->except('match')
			->every(function ($value) {
				return empty($value);
			});
	}
}
