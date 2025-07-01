<?php

namespace App\Services\Scraper\Factories;

use App\Services\Scraper\BaseScraper;


class VideoScraperFactory extends BaseScraper {

	public function scrape(): void {

		$chunks = $this->chunk($this->source);
		$this->collection = collect($chunks);
	}
}