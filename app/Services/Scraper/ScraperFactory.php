<?php

namespace App\Services\Scraper;

use App\Services\Scraper\Factories\PDFScraperFactory;
use App\Services\Scraper\Factories\TextareaScraperFactory;
use App\Services\Scraper\Factories\TextScraperFactory;
use App\Services\Scraper\Factories\WebsiteScraperFactory;
use App\Services\Scraper\Factories\VideoScraperFactory;
use Exception;

class ScraperFactory {
	/**
	 * @throws Exception
	 */
	public static function create(string $source, string $type): BaseScraper {
		return match ($type) {
			'file' => match (strtolower(pathinfo($source, PATHINFO_EXTENSION))) {
				'pdf' => new PDFScraperFactory($source, true),
				'txt' => new TextScraperFactory($source, true),
				'csv' => new TextScraperFactory($source, true),
				'json' => new TextScraperFactory($source, true),
				'html' => new TextScraperFactory($source, true),
				default => throw new Exception('Unsupported file type'),
			},
			'website' => new WebsiteScraperFactory($source),
			'text' => new TextareaScraperFactory($source),
			'video' => new VideoScraperFactory($source),
			default => throw new Exception('Unsupported content type'),
		};
	}
}
