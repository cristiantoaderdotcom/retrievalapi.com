<?php

namespace App\Livewire\App\KnowledgeBase\Resources;

use Exception;
use Livewire\Component;
use App\Models\Workspace;
use Illuminate\View\View;
use App\Models\KnowledgeBaseResource;
use App\Models\KnowledgeBaseVideoResource;
use Illuminate\Http\Request;
use Livewire\WithPagination;
use App\Enums\ResourceStatus;
use Livewire\WithFileUploads;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;
use Livewire\WithoutUrlPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Jobs\TrainSource;
use App\Traits\Livewire\App\KnowledgeBase\HasResources;

class Videos extends Component {
	use WithPagination, WithoutUrlPagination, WithFileUploads;
	use HasResources;

	public string $url = '';

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
			->whereHasMorph('resourceable', [KnowledgeBaseVideoResource::class])
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

		return view('livewire.app.knowledge-base.resources.videos', compact('resources', 'showTooltip'));
	}

	public function store(): void {
		$this->validate([
			'url' => 'required|string|min:20',
		]);

		try {
			//TODO: remove cache later
			$responseData = cache()->remember('video-transcript-' . data_get($this, 'url'), 60000, function () {
				$response = Http::withHeaders([
					'Content-Type' => 'application/json',
					'x-rapidapi-host' => 'video-transcript-scraper.p.rapidapi.com',
					'x-rapidapi-key' => config('services.rapidapi.key'),
				])->post('https://video-transcript-scraper.p.rapidapi.com/', [
					'video_url' => data_get($this, 'url')
				]);
	
				if (!$response->successful()) {
					throw new Exception('Failed to retrieve video transcript: ' . $response->status());
				}
	
				$responseData = $response->json();
				return $responseData;
			});

			$videoContent = data_get($responseData, 'text', '');
			$videoTitle = data_get($responseData, 'title', '');

			if (empty($videoContent)) {
				throw new Exception('No transcript content was found for this video');
			}

			DB::transaction(function () use ($videoContent, $videoTitle) {
				$videoResource = KnowledgeBaseVideoResource::query()
					->create([
						'url' => data_get($this, 'url'),
						'title' => $videoTitle,
						'content' => $videoContent,
						'workspace_id' => $this->workspace->id,
					]);

				$resource = new KnowledgeBaseResource([
					'workspace_id' => $this->workspace->id,
					'status' => ResourceStatus::PROCESSING,
					'process_started_at' => now(),
				]);

				$videoResource->resource()->save($resource);

				TrainSource::dispatchSync($resource);
			});

			$this->reset('url');
		} catch (Exception $e) {
			Log::error('Failed to store video resource', ['error' => $e->getMessage(), 'url' => data_get($this, 'url')]);
			$this->addError('url', 'Failed to extract video transcript: ' . $e->getMessage());
		}
	}
}
