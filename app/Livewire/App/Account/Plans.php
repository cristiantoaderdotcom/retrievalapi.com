<?php

namespace App\Livewire\App\Account;

use Livewire\Component;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class Plans extends Component {
	public string $plan = 'pro';

	public function mount() {
		if (auth()->user()->pro) {
			$this->redirect(route('app.index'));
		}
	}

	public function render() {
		return view('livewire.app.account.plans')
			->extends('layouts.app')
			->section('main');
	}

	public function store() {
		$this->validate([
			'plan' => 'required|string|in:standard,pro',
		]);

		$price_id = match ($this->plan) {
			'standard' => config('products.prices.standard'),
			'pro' => config('products.prices.pro'),
		};

		Stripe::setApiKey(config('cashier.secret'));

		$session = Session::create([
			'customer_email' => auth()->user()->email,
			'mode' => 'payment',
			'success_url' => route('app.index'),
			'cancel_url' => route('app.index'),
			'allow_promotion_codes' => true,
			'line_items' => [
				[
					'price' => $price_id,
					'quantity' => 1,
				],
			],
		]);

		return redirect($session->url);
	}
}
