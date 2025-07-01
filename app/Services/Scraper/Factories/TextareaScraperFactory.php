<?php

namespace App\Services\Scraper\Factories;

use App\Services\Scraper\BaseScraper;

class TextareaScraperFactory extends BaseScraper {
	public function scrape(): void {
		$chunks = $this->chunk($this->source);
		$this->collection = collect($chunks);
	}
}
