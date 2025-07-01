<?php

namespace App\Services\Crawler;

use Illuminate\Support\Uri;

abstract class BaseCrawler {
	protected array $urls = [];
	protected int $maxUrls = 50;

	public function __construct(protected Uri $url) {
	}

	abstract public function scrape(): void;


	public function urls(): array {
		return array_unique($this->urls);
	}

	protected function addUrl(string $url): void {
		$this->urls[] = $url;
	}
}