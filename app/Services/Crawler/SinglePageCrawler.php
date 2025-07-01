<?php

namespace App\Services\Crawler;

class SinglePageCrawler extends BaseCrawler {
	public function scrape(): void {
		$href = $this->url->__toString();
		if (filter_var($href, FILTER_VALIDATE_URL)) {
			$this->urls[] = $href;
		}
	}

	public function urls(): array {
		return $this->urls;
	}
}