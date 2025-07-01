<?php

namespace App\Livewire\App\Account;

use App\Models\Referral;
use App\Models\Session;
use Flux\Flux;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Contracts\View\View;

class Referrals extends Component {
	use WithPagination;

	public array $form = [
		'code' => '',
		'description' => '',
	];

	public array $filter = [
		'search' => '',
	];

	public function mount(): void {
		data_set($this->form, 'code', bin2hex(random_bytes(5)));
	}

	public function save(): void {
		$this->validate([
			'form.code' => 'required|string|max:64',
			'form.description' => 'nullable|string|max:255',
		]);

		Referral::create([
			'user_id' => Auth::id(),
			'code' => data_get($this->form, 'code'),
			'description' => data_get($this->form, 'description'),
		]);

		$this->modal('add-referral')->close();
	}

	public function render(): View {
		$referrals = Referral::query()
			->when(data_get($this->filter, 'search'), function ($query) {
				$query->where('code', 'like', '%' . data_get($this->filter, 'search') . '%')
					->orWhere('description', 'like', '%' . data_get($this->filter, 'search') . '%');
			})
			->withCount('referrers')

			->withSum('payments', 'amount_total')

			->withSum('paidPayments', 'amount_total')
			->withSum('pendingPayments', 'amount_total')
			->withSum('availablePayments', 'amount_total')

			->where('user_id', Auth::id())
			->paginate(10);

		return view('livewire.app.account.referrals', compact('referrals'))
			->extends('layouts.app')
			->section('main');
	}

	public function updated($property): void {
		$this->validateOnly($property);
	}

	public function rules(): array {
		return [
			'filter.search' => 'nullable|string',
		];
	}
}
