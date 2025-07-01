<?php

namespace App\Livewire\App\KnowledgeBase\Resources;

use App\Enums\ResourceStatus;
use App\Jobs\KnowledgeBase\ProcessResource;
use App\Jobs\TrainSource;
use App\Models\KnowledgeBaseResource;
use App\Models\KnowledgeBaseUrlResource;
use App\Models\Workspace;
use App\Services\Crawler\Crawler;
use App\Traits\Livewire\App\KnowledgeBase\HasResources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Jobs\KnowledgeBase\PrioritizeUrlsJob;
use App\Jobs\KnowledgeBase\ProcessTopUrlsJob;
use Illuminate\Bus\Chain;

class Links extends Component {
	use WithPagination, WithoutUrlPagination;
	use HasResources;

	public string $link = '';
	public bool $lookup = true;

	public bool $polling = false;

	#[Locked]
	public Workspace $workspace;

	private bool $contexts = false;

	private bool $locked = false;

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
			->whereHasMorph('resourceable', [KnowledgeBaseUrlResource::class])
			->when(!empty($this->filters['status']), function ($query) {
				$query->where('status', $this->filters['status']);
			}, function ($query) {
				$query->where(function($query) {
					$query->whereNull('status')
						->orWhereNot('status', ResourceStatus::HIDDEN);
				});
			})
			// Join with the URL resource table to order by priority_score
			->join('knowledge_base_url_resources', function ($join) {
				$join->on('knowledge_base_url_resources.id', '=', 'knowledge_base_resources.resourceable_id')
					->where('knowledge_base_resources.resourceable_type', '=', KnowledgeBaseUrlResource::class);
			})
			->orderByDesc('knowledge_base_url_resources.priority_score')
			->orderByDesc('knowledge_base_url_resources.is_primary')
			->select('knowledge_base_resources.*') // Select only from the main resources table
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

		return view('livewire.app.knowledge-base.resources.links', compact('resources', 'showTooltip'));
	}

	public function store(): void {

		$this->validate([
			'link' => 'required|url',
			'lookup' => 'nullable|boolean'
		]);

		try {
			$crawler = new Crawler(data_get($this, 'link'), data_get($this, 'lookup'));
			$crawler->execute();

			$urlChunks = collect($crawler->urls())->chunk(100);
			$autoProcess = $urlChunks->first()?->count() <= 1;

			DB::transaction(function () use ($urlChunks, $autoProcess, $crawler) {
				$urlChunks->each(function ($urls) use ($autoProcess) {
					// First check if any of these URLs already exist for this workspace
					$existingUrls = KnowledgeBaseUrlResource::query()
						->where('workspace_id', $this->workspace->id)
						->whereIn('url', $urls)
						->pluck('url')
						->toArray();
					
					// Filter out existing URLs
					$newUrls = collect($urls)->filter(function($url) use ($existingUrls) {
						return !in_array($url, $existingUrls);
					});
					
					if ($newUrls->isEmpty()) {
						return;
					}
					
					$urlResourceIds = $newUrls->map(function ($url) {
						// Set the original link as primary with priority score 100
						$data = [
							'url' => $url,
							'workspace_id' => $this->workspace->id
						];
						
						if ($url === data_get($this, 'link')) {
							$data['is_primary'] = true;
							$data['priority_score'] = 100;
						}
						
						return KnowledgeBaseUrlResource::query()->insertGetId($data);
					});

					$resources = $urlResourceIds
						->map(fn($id) => [
							'resourceable_type' => KnowledgeBaseUrlResource::class,
							'resourceable_id' => $id,
							'workspace_id' => $this->workspace->id,
							'created_at' => now(),
							'updated_at' => now()
						])
						->toArray();

					if ($autoProcess) {
						$resourceIds = KnowledgeBaseResource::query()
							->insertGetId($resources[0]);

						$resource = KnowledgeBaseResource::query()
							->where('id', $resourceIds)
							->first();

						$resource->update([
							'status' => ResourceStatus::PROCESSING,
							'process_started_at' => now(),
							'process_completed_at' => null,
						]);

						TrainSource::dispatch($resource);
					} else {
						KnowledgeBaseResource::query()
							->insert($resources);
					}
				});

				// Get all URLs except the primary one for prioritization
				$allUrls = collect($crawler->urls())->filter(fn($url) => $url !== data_get($this, 'link'));
				
				if ($allUrls->isNotEmpty()) {
					// Create a job chain to first prioritize URLs then process the top ones
					$urlChunks = $allUrls->chunk(100);
					
					// If we have multiple chunks, use job chaining
					if ($urlChunks->count() > 1) {
						// Create jobs for all chunks after the first one
						$chainedJobs = $urlChunks->slice(1)
							->map(function($chunk) {
								return new PrioritizeUrlsJob($chunk, $this->workspace);
							})
							->push(new ProcessTopUrlsJob($this->workspace, maxUrlsToProcess: 25))
							->all();
						
						// Dispatch the first job with the rest of the chain
						PrioritizeUrlsJob::withChain($chainedJobs)
							->dispatch($urlChunks->first(), $this->workspace);
							
						Log::info("Dispatched job chain to prioritize and process URLs for workspace {$this->workspace->id}");
					} else {
						// Just dispatch the single prioritize job followed by processing
						PrioritizeUrlsJob::withChain([
							new ProcessTopUrlsJob($this->workspace, 25)
						])->dispatch($urlChunks->first(), $this->workspace);
						
						Log::info("Dispatched simple job chain to prioritize and process URLs for workspace {$this->workspace->id}");
					}
				}
			});
		} catch (\Exception $e) {
			Log::info('Failed to store resources', ['error' => $e->getMessage()]);
			$this->addError('link', 'Failed to store resources. Please try again.');
		}

		$this->reset('link');
	}
}
