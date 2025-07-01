<?php

namespace App\Services\Scraper\Factories;

use App\Services\Scraper\BaseScraper;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\PdfToText\Exceptions\PdfNotFound;
use Spatie\PdfToText\Pdf;

class TextScraperFactory extends BaseScraper {
	/**
	 * @throws PdfNotFound
	 * @throws ConnectionException
	 */
	public function scrape(): void {
		$text = file_get_contents($this->source);

		$chunks = $this->chunk($text);
		$this->collection = collect($chunks);
	}
}