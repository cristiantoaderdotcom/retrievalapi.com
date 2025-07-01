<?php

namespace App\Livewire\App\KnowledgeBase\Resources;

use Exception;
use Livewire\Component;
use App\Models\Workspace;
use Illuminate\View\View;
use App\Models\KnowledgeBaseResource;
use App\Models\KnowledgeBaseTextResource;
use Illuminate\Http\Request;
use Livewire\WithPagination;
use App\Enums\ResourceStatus;
use Livewire\WithFileUploads;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;
use Livewire\WithoutUrlPagination;
use Illuminate\Support\Facades\Log;
use App\Jobs\TrainSource;
use App\Traits\Livewire\App\KnowledgeBase\HasResources;

class Texts extends Component {
	use WithPagination, WithoutUrlPagination, WithFileUploads;
	use HasResources;

	public string $content = '';

	public bool $polling = false;
	private bool $locked = false;

	#[Locked]
	public Workspace $workspace;

	public function mount($workspace): void {
		$this->workspace = $workspace;
		$this->statuses = ResourceStatus::toArray();
	}

	public function render(Request $request): View {
		$resources = $this->workspace
			->resources()
			->withWhereHas('resourceable', function ($query) {
				$query->when($this->filters['match'] && $this->filters['search'], function ($query) {
					$match = $this->filters['match'];
					$search = $this->filters['search'];
					if ($match === 'contains') {
						$query->where('url', 'like', '%' . $search . '%');
					} elseif ($match === 'not_contains') {
						$query->where('url', 'not like', '%' . $search . '%');
					} elseif ($match === 'starts') {
						$query->where('url', 'like', $search . '%');
					} elseif ($match === 'ends') {
						$query->where('url', 'like', '%' . $search);
					}
				});
			})
			->whereHasMorph('resourceable', [KnowledgeBaseTextResource::class])
			->when(!empty($this->filters['status']), function ($query) {
				$query->where('status', $this->filters['status']);
			}, function ($query) {
				$query->where(function($query) {
					$query->whereNull('status')
						->orWhereNot('status', ResourceStatus::HIDDEN);
				});
			})
			->orderByDesc('created_at')
			->paginate(10);

		$this->processing = $this->workspace
			->resources()
			->where(function ($query) {
				$query->whereNot('status', ResourceStatus::FAILED)
					->whereNotNull('process_started_at')
					->whereNull('process_completed_at');
			})
			->count();

		$this->processed = $this->workspace
			->resources()
			->where('status', ResourceStatus::PROCESSED)
			->count();

		$this->failed = $this->workspace
			->resources()
			->where('status', ResourceStatus::FAILED)
			->count();

		$this->polling = !empty($this->processing);

		if(empty($this->processed)) {
			$this->dispatch('lock-steps', true);
		} else if(!$this->polling) {
			$this->dispatch('lock-steps', false);
		}

		if ($this->locked !== $this->polling) {
			$this->locked = $this->polling;
			$this->dispatch('lock-steps', true);
		}

		$showTooltip = !KnowledgeBaseResource::query()
			->where('workspace_id', $this->workspace->id)
			->whereNotNull('status')
			->exists();

			return view('livewire.app.knowledge-base.resources.texts', compact('resources', 'showTooltip'));
	}

	public function store(): void {

		$this->validate([
			'content' => 'required|string|min:100',
		]);

		try {
			DB::transaction(function () {
				$textResource = KnowledgeBaseTextResource::query()
					->create([
						'content' => data_get($this, 'content'),
					]);

				$resource = new KnowledgeBaseResource([
					'workspace_id' => $this->workspace->id,
				]);

				$textResource->resource()->save($resource);

				TrainSource::dispatch($resource);
			});
		} catch (\Exception $e) {
			Log::info('Failed to store resources', ['error' => $e->getMessage()]);
			$this->addError('form.link', 'Failed to store resources. Please try again.');
		}

		$this->reset('content');
	}
}
