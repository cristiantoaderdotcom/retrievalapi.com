<?php

namespace App\Jobs;

use App\Models\KnowledgeBase;
use App\Models\KnowledgeBaseResource;
use App\Models\KnowledgeBaseResourceDuplicate;
use App\Models\KnowledgeBaseTextResource;
use App\Models\KnowledgeBaseFileResource;
use App\Models\KnowledgeBaseUrlResource;
use App\Models\KnowledgeBaseVideoResource;
use App\Enums\ResourceStatus;
use App\Services\Scraper\Scraper;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TrainSource implements ShouldQueue {
	use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

	private KnowledgeBaseResource $resource;

	/**
	 * Create a new job instance.
	 */
	public function __construct($resource) {
		$this->resource = $resource;
		$this->resource->loadMissing('resourceable');
	}

	public function handle(): void {

		try {
			$scrape = $this->initializeScraper();

			DB::transaction(function () use ($scrape) {
				$charactersCount = $scrape->getCharactersCount();

				$this->resource->update([
					'words_count' => $scrape->getWordsCount(),
					'characters_count' => $charactersCount,
				]);

				$user = $this->resource->workspace->user;
				
				$user->update([
					'context_limit' => max(0, $user->context_limit - $charactersCount),
				]);
			});

			$jobs = [];


			Log::info($scrape->getCollection());

			$scrape->getCollection()
				->each(function ($text) use (&$jobs) {
					if ($this->resource->resourceable instanceof KnowledgeBaseResource) {
						$text = $this->checkTextDuplicates($text);
					}

					if(empty($text)) {
						return;
					}

					$jobs[] = new ProcessChunk($this->resource, $text);
				});

			if(empty($jobs)) {
				$this->resource->update([
					'status' => ResourceStatus::PROCESSED,
					'error_message' => null,
					'process_completed_at' => now(),
				]);
				return;
			}

			Bus::chain($jobs)->dispatch();
		} catch (Exception|Throwable $e) {
			$this->resource->update([
				'status' => ResourceStatus::FAILED,
				'error_message' => $e->getMessage(),
				'process_completed_at' => now(),
			]);

			Log::error('Error processing resource', ['exception' => $e]);
		}
	}

	public function failed(Exception $exception): void {
		$this->resource->update([
			'status' => ResourceStatus::FAILED,
		]);
	}

	/**
	 * @throws Exception
	 */
	private function initializeScraper(): Scraper {
		return match (true) {
			$this->resource->resourceable instanceof KnowledgeBaseUrlResource => new Scraper($this->resource->resourceable->url, 'website'),
			$this->resource->resourceable instanceof KnowledgeBaseFileResource => new Scraper(public_path($this->resource->resourceable->path), 'file'),
			$this->resource->resourceable instanceof KnowledgeBaseTextResource => new Scraper($this->resource->resourceable->content, 'text'),
			$this->resource->resourceable instanceof KnowledgeBaseVideoResource => new Scraper($this->resource->resourceable->content, 'video'),
			default => throw new Exception('Unsupported model type'),
		};
	}

	private function checkTextDuplicates($text): string {
		$texts = collect(explode("\n", $text))
			->filter(function ($value) {
				return !empty($value);
			});

		$textsToRemove = collect();

		$texts->each(function ($value) use (&$textsToRemove) {
			$text = KnowledgeBaseResourceDuplicate::query()
				->firstOrCreate([
					'knowledge_base_id' => $this->resource->knowledge_base_id,
					'text' => $value,
				]);

			if ($text->wasRecentlyCreated === false) {
				$textsToRemove->push($value);
			}
		});

		$texts = $texts->reject(function ($value) use ($textsToRemove) {
			return $textsToRemove->contains($value);
		});

		return $texts->implode("\n");
	}
}
