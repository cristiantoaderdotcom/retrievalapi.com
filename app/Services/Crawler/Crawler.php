<?php

namespace App\Services\Crawler;

use Illuminate\Support\Uri;
use InvalidArgumentException;

class Crawler {
	private BaseCrawler $crawler;

	private bool $executed = false;

	public function __construct(string $url) {
		/*$host = parse_url($url, PHP_URL_HOST);
		if ($host && (!checkdnsrr($host, 'A') && !checkdnsrr($host, 'AAAA'))) {
			throw new InvalidArgumentException('Invalid URL');
		}*/

		$this->crawler = CrawlerFactory::create(Uri::of($url));
	}

	public function execute(): void {
		if ($this->executed) {
			return;
		}

		$this->crawler->scrape();

		$this->executed = true;
	}

	public function urls(): array {
		if (!$this->executed) {
			throw new InvalidArgumentException('Crawler must be executed before accessing URLs');
		}

		return $this->crawler->urls();
	}
}
