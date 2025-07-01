<?php

namespace App\Services\Scraper\Factories;

use App\Services\Scraper\BaseScraper;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\PdfToText\Exceptions\PdfNotFound;
use Spatie\PdfToText\Pdf;

class PDFScraperFactory extends BaseScraper {
	/**
	 * @throws PdfNotFound
	 * @throws ConnectionException
	 */
	public function scrape(): void {
		$file = $this->isFile ? $this->source : $this->fetchPdfContent();

		$text = (new Pdf(config('services.poppler.bin')))
			->setPdf($file)
			->text();

		$chunks = $this->chunk($text);
		$this->collection = collect($chunks);
	}

	/**
	 * @throws ConnectionException
	 */
	private function fetchPdfContent(): string {
		$response = Http::timeout(10)
			->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36')
			->get($this->source);

		if ($response->status() !== 200) {
			Log::error($response->body());
			throw new ConnectionException('Failed to connect to the website.');
		}

		return $response->body();
	}
}