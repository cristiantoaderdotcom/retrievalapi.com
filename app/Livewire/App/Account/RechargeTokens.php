<?php

namespace App\Livewire\App\Account;

use App\Models\User;
use App\Traits\Livewire\App\HasIcon;
use Flux\Flux;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class RechargeTokens extends Component {
	public string $token_package = 'recharge-500';


	public function render() {
		return view('livewire.app.account.recharge-tokens')
			->extends('layouts.app')
			->section('main');
	}

	public function store() {
		$this->validate([
			'token_package' => 'required|string|in:recharge-50,recharge-250,recharge-500',
		]);

		$price_id = match ($this->token_package) {
			'recharge-50' => config('products.prices.recharge-50'),
			'recharge-250' => config('products.prices.recharge-250'),
			'recharge-500' => config('products.prices.recharge-500'),
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

		$this->redirect($session->url);
	}
}
