<?php

use Illuminate\Support\Number;
use Carbon\Carbon;

if (!function_exists('wrap_characters_in_span')) {
	function wrap_characters_in_span($string): string {
		$characters = str_split($string);

		$result = '';
		foreach ($characters as $character) {
			$result .= ($character == ' ') ? $character : '<span>' . $character . '</span>';
		}

		return $result;
	}
}

if (!function_exists('normalize_url')) {
	function normalize_url(string $url, bool $ssl = true): string {
		$url = trim($url);

		if (!str_contains($url, "//")) {
			$url = "//" . $url;
		}

		$parsedUrl = parse_url($url);

		$host = isset($parsedUrl['host']) ? str_ireplace('www.', '', $parsedUrl['host']) : '';

		$path = $parsedUrl['path'] ?? '';

		return ($ssl ? 'https://' : '//') . $host . $path;
	}
}

if (!function_exists('acronym')) {
	function acronym(string $string): string {
		$words = array_filter(explode(' ', $string));

		if (count($words) === 1) {
			return strtoupper(substr($words[0], 0, 2));
		}

		$initials = collect($words)
			->map(fn($word) => strtoupper($word[0]))
			->implode('');

		return substr($initials, 0, 2);
	}
}

if (!function_exists('getRefererInfo')) {
	function getRefererInfo($referer, bool $onlyQueryString = false): string {
		if (!$referer) {
			return '';
		}

		$parsedUrl = parse_url($referer);

		if ($onlyQueryString) {
			return $parsedUrl['query'] ?? '';
		}

		return ($parsedUrl['scheme'] ?? 'https') . '://' .
			($parsedUrl['host'] ?? '') .
			rtrim($parsedUrl['path'] ?? '', '/');
	}
}

if (!function_exists('format_number')) {
	function format_number(int $number): string {
		return $number < 1000 ? (string) $number : Number::abbreviate($number, precision: 1);
	}
}


if (!function_exists('format_date_range')) {
	function format_date_range($start, $end): string {
		$start = Carbon::parse($start);
		$end = Carbon::parse($end);

		return $start->format('M j') . ' – ' . $end->format('M j, Y');
	}
}