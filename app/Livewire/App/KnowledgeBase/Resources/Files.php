<?php

namespace App\Livewire\App\KnowledgeBase\Resources;

use App\Enums\ResourceStatus;
use App\Jobs\KnowledgeBase\ProcessResource;
use App\Jobs\TrainSource;
use App\Models\KnowledgeBaseFileResource;
use App\Models\KnowledgeBaseResource;
use App\Models\Workspace;
use App\Traits\Livewire\App\KnowledgeBase\HasResources;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Files extends Component {
	use WithPagination, WithoutUrlPagination, WithFileUploads;
	use HasResources;

	public array $attachments = [];

	public bool $polling = false;
	private bool $locked = false;

	#[Locked]
	public 	Workspace $workspace;

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
			->whereHasMorph('resourceable', [KnowledgeBaseFileResource::class])
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

		return view('livewire.app.knowledge-base.resources.files', compact('resources', 'showTooltip'));
	}

	public function updatedAttachments(): void {
		$this->store();
		$this->attachments = [];
	}

	public function store(): void {
		$this->validate([
			'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,txt',
		]);

		try {
			DB::transaction(function () {
				foreach ($this->attachments as $file) {
					$path = $file->store('uploads/users/' . auth()->id() . '/workspace/' . $this->workspace->id, 'public');

					$fileResource = KnowledgeBaseFileResource::query()
						->create([
							'name' => $file->getClientOriginalName(),
							'path' => "storage/$path", //TODO: Check this, file is not deleted
							'type' => $file->getClientMimeType(),
							'size' => $file->getSize()
						]);

					$resource = new KnowledgeBaseResource([
						'workspace_id' => $this->workspace->id,
					]);

					$fileResource->resource()->save($resource);

					TrainSource::dispatch($resource);
				}
			});
		} catch (Exception $e) {
			Log::error(__LINE__, ['Exception: ' => $e->getMessage()]);
			$this->addError('attachments', 'Failed to upload files');
		}

		$this->attachments = [];
	}
}
