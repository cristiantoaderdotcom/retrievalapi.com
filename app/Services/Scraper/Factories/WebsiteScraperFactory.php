<?php

namespace App\Services\Scraper\Factories;

use App\Services\Scraper\BaseScraper;
use DOMDocument;
use DOMNode;
use DOMXPath;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebsiteScraperFactory extends BaseScraper {
	private const MINIMUM_WORD_COUNT = 6;

	private string $text = '';
	private array $links = [];
	private string $currentUrl = '';

	/**
	 * @throws ConnectionException
	 */
	public function scrape(): void {
		$response = Http::timeout(10)
			->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36')
			->get($this->source);

		if($response->status() !== 200) {
			Log::error($response->body());
			throw new ConnectionException('Failed to connect to the website.');
		}

		$this->currentUrl = $this->source;
		$this->processBody($response->body());
	}

	private function processBody(string $html): void {
		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_clear_errors();

		$xpath = new DOMXPath($dom);

		$this->extractMetaDescription($xpath);
		$this->extractTextContent($xpath);
		$this->extractLinks($xpath);

		$chunks = $this->chunk($this->text);
		$this->collection = collect($chunks);
	}

	private function extractMetaDescription(DOMXPath $xpath): void {
		$nodes = $xpath->query('//meta[@name="description"]');
		foreach ($nodes as $node) {
			$content = $this->sanitize($node->getAttribute('content'));
			if (!empty($content)) {
				$this->text .= $content . PHP_EOL;
			}
		}
	}

	private function extractTextContent(DOMXPath $xpath): void {
		$nodes = $xpath->query('//title | //h1 | //h2 | //h3 | //h4 | //h5 | //p | //li | //span');
		foreach ($nodes as $node) {
			$text = $this->sanitize($node->textContent);
			if (!empty($text) && str_word_count($text) >= self::MINIMUM_WORD_COUNT) {
				$this->text .= $text . PHP_EOL;
				
				// Check if this node or any of its parent elements contains a link
				$this->findAndRecordLinks($node, $text);
			}
		}
	}
	
	private function extractLinks(DOMXPath $xpath): void {
		$linkNodes = $xpath->query('//a[@href]');
		foreach ($linkNodes as $node) {
			$href = $node->getAttribute('href');
			$text = $this->sanitize($node->textContent);
			
			if (!empty($text) && !empty($href) && str_word_count($text) >= self::MINIMUM_WORD_COUNT) {
				$absoluteUrl = $this->makeAbsoluteUrl($href);
				if (!empty($absoluteUrl)) {
					$this->links[] = [
						'text' => $text,
						'url' => $absoluteUrl
					];
				}
			}
		}
		
		// Append links to text with actual URLs
		if (!empty($this->links)) {
			$this->text .= PHP_EOL . "--- REFERENCE LINKS ---" . PHP_EOL;
			foreach ($this->links as $link) {
				$this->text .= $link['text'] . " | " . $link['url'] . PHP_EOL;
			}
		}
	}
	
	private function findAndRecordLinks(DOMNode $node, string $text): void {
		// Check if this node is an anchor or has anchor children
		$currentNode = $node;
		$xpath = new DOMXPath($node->ownerDocument);
		
		// Check if node itself contains a link
		$anchors = $xpath->query('.//a[@href]', $node);
		if ($anchors->length > 0) {
			foreach ($anchors as $anchor) {
				$href = $anchor->getAttribute('href');
				$anchorText = $this->sanitize($anchor->textContent);
				
				if (!empty($href) && !empty($anchorText)) {
					$absoluteUrl = $this->makeAbsoluteUrl($href);
					if (!empty($absoluteUrl)) {
						// Add the URL directly in the text
						$this->text .= " (" . $absoluteUrl . ") ";
						
						$this->links[] = [
							'text' => $anchorText,
							'url' => $absoluteUrl
						];
					}
				}
			}
		}
		
		// Check parent nodes for links
		while ($currentNode = $currentNode->parentNode) {
			if ($currentNode->nodeName === 'a' && $currentNode->hasAttribute('href')) {
				$href = $currentNode->getAttribute('href');
				$absoluteUrl = $this->makeAbsoluteUrl($href);
				
				if (!empty($absoluteUrl)) {
					// Add the URL directly in the text
					$this->text .= " (" . $absoluteUrl . ") ";
					
					$this->links[] = [
						'text' => $text,
						'url' => $absoluteUrl
					];
				}
				break;
			}
		}
	}
	
	private function makeAbsoluteUrl(string $url): string {
		if (empty($url) || $url === '#' || str_starts_with($url, 'javascript:')) {
			return '';
		}
		
		// If it's already absolute, return it
		if (filter_var($url, FILTER_VALIDATE_URL)) {
			return $url;
		}
		
		// Handle relative URLs
		$baseUrlParts = parse_url($this->currentUrl);
		
		if (!$baseUrlParts) {
			return '';
		}
		
		$scheme = $baseUrlParts['scheme'] ?? 'https';
		$host = $baseUrlParts['host'] ?? '';
		
		if (empty($host)) {
			return '';
		}
		
		// Handle URLs that start with //
		if (str_starts_with($url, '//')) {
			return $scheme . ':' . $url;
		}
		
		// Handle URLs that start with /
		if (str_starts_with($url, '/')) {
			return $scheme . '://' . $host . $url;
		}
		
		// Handle URLs that are relative to the current path
		$path = $baseUrlParts['path'] ?? '';
		$path = substr($path, 0, strrpos($path, '/') + 1);
		
		return $scheme . '://' . $host . $path . $url;
	}

	private function sanitize(string $text): string {
		return trim(preg_replace('/\s+/', ' ', $text));
	}
}
