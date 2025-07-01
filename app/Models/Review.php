<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Uri;

class Review extends Model {
	protected $fillable = [
		'name',
		'title',
		'text',
		'avatar'
	];

	public function getSourceDetailsAttribute(): ?array {
		if (!$this->source) {
			return null;
		}

		$uri = Uri::of($this->source);
		$host = $uri->host();
		$username = $uri->path();

		$icon = collect([
			'instagram' => 'https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg',
			'facebook' => 'https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg',
		])->first(function ($url, $key) use ($host) {
			return str_contains($host, $key);
		}, '');

		return [
			'icon' => $icon,
			'username' => $username,
		];
	}
}
