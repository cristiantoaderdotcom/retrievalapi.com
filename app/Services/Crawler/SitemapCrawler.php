<?php

namespace App\Services\Crawler;

class SitemapCrawler extends BaseCrawler {
	public function scrape(): void {
		$this->urls[] = 'test sitemap';
	}

	public function urls(): array {
		return $this->urls;
	}
}