<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasDynamicSettings {
	public function setting(string $key, $default = []) {
		$rootKey = Str::before($key, '.');
		$setting = $this->settings->firstWhere('key', $rootKey);

		if (!$setting) {
			return $default;
		}

		$value = $this->parseSettingValue($setting->value);

		if (!Str::contains($key, '.')) {
			return is_array($value) && is_array($default) ? 
				array_replace_recursive($default, $value) : 
				$value;
		}

		$path = Str::after($key, $rootKey . '.');
		$settingValue = Arr::get($value, $path, []);

		return is_array($settingValue) && is_array($default) ? 
			array_replace_recursive($default, $settingValue) : 
			$settingValue;
	}

	private function parseSettingValue($value) {
		if (!is_string($value)) {
			return $value;
		}

		return Str::isJson($value) ? json_decode($value, true) : $value;
	}
}