<?php

namespace App\Services\Scraper;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;

abstract class BaseScraper {
	public Collection $collection;
	protected string $source;
	protected bool $isFile;

	public function __construct(string $source, bool $isFile = false) {
		$this->source = $source;
		$this->isFile = $isFile;
		$this->collection = collect();
	}

	abstract public function scrape(): void;

	protected function chunk(string $text, $size = 5000): array {
		$sentences = collect(preg_split('/(?<=[.])\s+/', $text));
		$chunks = [];
		$currentChunk = '';

		foreach ($sentences as $sentence) {
			$tempChunk = ($currentChunk ? $currentChunk . ' ' : '') . $sentence;

			if (Str::length($tempChunk) <= $size) {
				$currentChunk = $tempChunk;
			} else {
				$chunks[] = $currentChunk;
				$currentChunk = $sentence;
			}
		}

		if ($currentChunk) {
			$chunks[] = $currentChunk;
		}

		return $chunks;
	}
}
