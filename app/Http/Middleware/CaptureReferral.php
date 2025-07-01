<?php

namespace App\Http\Middleware;

use App\Models\Referral;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureReferral {
	/**
	 * Handle an incoming request.
	 *
	 * @param \Closure(Request): (Response) $next
	 */
	public function handle(Request $request, Closure $next): Response {
		if (!empty($request->referral) && !$request->session()->has('referral')) {
			Referral::query()
				->where('code', (string) $request->referral)
				->increment('clicks');

			session()->put('referral', $request->referral);
		}

		return $next($request);
	}
}
