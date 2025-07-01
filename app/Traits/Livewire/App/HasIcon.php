<?php

namespace App\Traits\Livewire\App;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

trait HasIcon {
	use WithFileUploads;

	#[Validate('image|max:1024|mimes:jpeg,png,jpg,gif,webp')]
	public $file;

	protected int $defaultQuality = 90;

	protected function handleIconUpload(string $settingKey = 'button.icon', ?string $folder = null): void {
		if ($this->file === null) {
			return;
		}

		try {
			$oldIcon = parse_url(data_get($this, $settingKey, ''), PHP_URL_PATH);
			$oldPath = $oldIcon ? ltrim($oldIcon, '/storage') : null;

			$path = $this->file->store('uploads/users/' . auth()->id() . '/' . ($folder ? $folder . '/' : '') . $settingKey, 'public');

			$optimizedPath = $this->optimizeImage($path);

			if ($optimizedPath) {
				data_set($this, $settingKey, url('storage/' . $optimizedPath));

				if ($oldPath) {
					$this->cleanupOldIcon($oldPath);
				}
			}
		} catch (Exception $e) {
			Log::error('Failed to handle icon upload', [
				'error' => $e->getMessage(),
				'setting_key' => $settingKey
			]);
		}
	}

	protected function optimizeImage(string $path, array $options = []): ?string {
		try {
			$extension = pathinfo($path, PATHINFO_EXTENSION);
			if (strtolower($extension) === 'gif' || strtolower($extension) === 'webp') {
				return $path;
			}

			$size = $options['size'] ?? $this->getDefaultIconSize();
			$quality = $options['quality'] ?? $this->defaultQuality;

			$imagePath = Storage::disk('public')->path($path);
			$manager = new ImageManager(new Driver());

			$image = $manager->read($imagePath);
			$image->scaleDown(width: $size);

			$width = $image->width();
			$height = $image->height();
			$x = ($size - $width) / 2;
			$y = ($size - $height) / 2;

			$canvas = $manager->create($size, $size);
			$canvas->place($image, 'top-left', intval($x), intval($y));

			$canvas->save($imagePath, quality: $quality);

			return $path;
		} catch (Exception $e) {
			Log::error('Image optimization failed', [
				'error' => $e->getMessage(),
				'path' => $path
			]);
			return null;
		}
	}

	protected function cleanupOldIcon(?string $oldPath): void {
		try {
			if ($oldPath && Storage::disk('public')->exists($oldPath)) {
				Storage::disk('public')->delete($oldPath);
			}
		} catch (Exception $e) {
			Log::error('Failed to cleanup old icon', [
				'error' => $e->getMessage(),
				'path' => $oldPath
			]);
		}
	}

	protected function getDefaultIconSize(int $size = 32): int {
		return $size;
	}

	protected function deleteIcon(string $settingKey = 'button.icon'): void {
		try {
			$icon = parse_url(data_get($this, $settingKey, ''), PHP_URL_PATH);
			$path = $icon ? ltrim($icon, '/storage') : null;

			if ($path) {
				$this->cleanupOldIcon($path);
				data_set($this, $settingKey, '');
			}
		} catch (Exception $e) {
			Log::error('Failed to delete icon', [
				'error' => $e->getMessage(),
				'setting_key' => $settingKey
			]);
		}
	}
}
