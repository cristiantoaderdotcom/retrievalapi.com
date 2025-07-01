<?php

namespace App\Services\Crawler;

use DOMDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;

class MultiPageCrawler extends BaseCrawler {
	private const BLACKLIST = ['javascript:', 'mailto:', 'tel:', 'email-protection'];

	private array $visited = [];
	private array $queue = [];

	public function scrape(): void {
		$this->queue[] = $this->url->__toString();
		$this->queue[] = $this->url->withPath('/help')->__toString();
		$this->queue[] = $this->url->withPath('/blog')->__toString();

		while (!empty($this->queue) && count(array_unique($this->urls)) < $this->maxUrls) {
			$url = array_shift($this->queue);

			if (isset($this->visited[$url])) {
				continue;
			}

			$this->visited[$url] = true;
			$this->crawlPage($url);
		}
	}

	private function crawlPage(string $url): void {
		try {
			$response = Http::timeout(5)
				->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36')
				->get($url);

			if ($response->status() !== 200) {
				return;
			}

			$content = $response->body();

			$newUrls = $this->extractUrls($content);

			foreach ($newUrls as $newUrl) {
				if (!isset($this->visited[$newUrl])) {
					$this->queue[] = $newUrl;
				}

				$this->addUrl($newUrl);
			}
		} catch (\Exception $e) {
			error_log("Error crawling {$url}: " . $e->getMessage());
		}
	}

	//TODO: <a href="../index.php" accesskey="1" title="">Acasa</a> is not a valid URL, get only absolute URLs
	private function extractUrls(string $content): array {
		$urls = [];

		libxml_use_internal_errors(true);
		$dom = new DOMDocument();
		$dom->loadHTML($content);
		libxml_clear_errors();

		$links = $dom->getElementsByTagName('a');

		foreach ($links as $link) {
			$href = $link->getAttribute('href');

			if (empty($href)) {
				//dump("Skipping empty href: $href");
				continue;
			}

			foreach (self::BLACKLIST as $blacklisted) {
				if (str_contains($href, $blacklisted)) {
					continue 2;
				}
			}

			$uri = Uri::of($href);

			if ($uri->host() === null) {
				$uri = $this->url->withPath($uri->path());
			}

			$href = strtok($uri->__toString(), '?');

			if (!filter_var($href, FILTER_VALIDATE_URL)) {
				//dump("Skipping $href because it's not a valid URL");
				continue;
			}

			if (str_contains($href, '#')) {
				continue;
			}

			$host = preg_replace('/^www\./', '', $uri->host());

			if (!str_contains($this->url, $host)) {
				//dump("Skipping $href because it's not on the same domain");
				continue;
			}

			$href = rtrim($href, '/');

			$urls[] = $href;
		}

		return $urls;
	}
}
