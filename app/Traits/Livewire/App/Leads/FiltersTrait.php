<?php

namespace App\Traits\Livewire\App\Leads;

use Illuminate\Support\Carbon;

trait FiltersTrait {
	public array $filters = [];
	public ?int $block_id;

	public function filterWidget($query): void {
		$query->where('user_id', auth()->user()->id);

		$query->when(data_get($this->filters, 'widget'), function ($query) {
			$query->where('uuid', data_get($this->filters, 'widget'));
		});
	}

	public function filterSearch($query): void {
		$search = data_get($this->filters, 'search');

		$query->where(function ($query) use ($search) {
			$query->where('name', 'like', '%' . $search . '%')
				->orWhere('email', 'like', '%' . $search . '%')
				->orWhere('phone', 'like', '%' . $search . '%');
		});
	}

	public function filterDate($query) {
		$date = data_get($this->filters, 'date');

		$query->whereBetween('created_at', [
			Carbon::parse(data_get($date, 'start'))->startOfDay(),
			Carbon::parse(data_get($date, 'end'))->endOfDay()
		]);
	}
}