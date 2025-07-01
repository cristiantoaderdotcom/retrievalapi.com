<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jaybizzle\LaravelCrawlerDetect\Facades\LaravelCrawlerDetect;
use Symfony\Component\HttpFoundation\Response;

class PreventSessionForBots {
	/**
	 * Handle an incoming request.
	 *
	 * @param \Closure(Request): (Response) $next
	 */
	public function handle(Request $request, Closure $next): Response {
		if (LaravelCrawlerDetect::isCrawler())
			config(['session.driver' => 'array']);

		return $next($request);
	}
}
