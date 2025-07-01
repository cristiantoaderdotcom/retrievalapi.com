<?php

namespace App\Services\Scraper;

use Illuminate\Support\Collection;

class Scraper {
	private BaseScraper $scraper;
	private bool $executed = false;

	/**
	 * @throws \Exception
	 */
	public function __construct(string $source, string $type) {
		$this->scraper = ScraperFactory::create($source, $type);
		$this->execute();
	}

	private function execute(): void {
		if ($this->executed) {
			return;
		}

		$this->scraper->scrape();
		$this->executed = true;
	}

	public function getCollection(): Collection {
		return $this->scraper->collection;
	}

	public function getWordsCount(): int {
		return $this->scraper->collection->reduce(fn($carry, $item) => $carry + str_word_count($item), 0);
	}

	public function getCharactersCount(): int {
		return $this->scraper->collection->reduce(fn($carry, $item) => $carry + strlen($item), 0);
	}
}
