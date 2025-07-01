<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Illuminate\Support\Str;

class PaymentController extends Controller {
	public function checkout(Request $request) {
		Stripe::setApiKey(config('cashier.secret'));

		$uuid = Str::uuid();

		$session = Session::create([
			'metadata' => [
				'uuid' => $uuid,
				'referral' => session()->get('referral'),
			],
			'mode' => 'payment',
			'success_url' => route('thank-you', $uuid),
			'cancel_url' => route('home'),
			'allow_promotion_codes' => true,
			'line_items' => [
				[
					'price' => config('products.prices.standard'),
					'quantity' => 1,
				],
			],
		]);

		return redirect($session->url);
	}
}
