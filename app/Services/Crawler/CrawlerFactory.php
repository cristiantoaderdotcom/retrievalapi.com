<?php

namespace App\Services\Crawler;

use Illuminate\Support\Str;
use Illuminate\Support\Uri;

class CrawlerFactory {
	public static function create(Uri $uri): BaseCrawler {
		$path = Str::of($uri->path())
			->trim('/')
			->value();

		if (Str::contains($path, 'xml') || Str::contains($path, 'sitemap')) {
			return new SitemapCrawler($uri);
		}

		if (empty($path)) {
			return new MultiPageCrawler($uri);
		}

		return new SinglePageCrawler($uri);
	}
}