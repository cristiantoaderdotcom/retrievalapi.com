<?php

namespace App\Listeners;

use App\Models\Referral;
use App\Models\User;
use App\Models\UserPayment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;
use Illuminate\Support\Str;
use Stripe\Checkout\Session;
use Stripe\Invoice;
use Stripe\Stripe;

class StripeEventListener {
	/**
	 * Create the event listener.
	 */
	public function __construct() {
		//
	}

	/**
	 * Handle the event.
	 */
	public function handle(WebhookReceived $event): void {
		match ($event->payload['type']) {
			'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event),
			'charge.refunded' => $this->handleChargeRefunded($event),
			default => null,
		};
	}

	private function handleCheckoutSessionCompleted($event): void {
		$session = $event->payload['data']['object'];

		$customer = data_get($session, 'customer_details');
		$password = Str::random(10);

		$user = User::query()->firstOrCreate([
			'email' => $customer['email'],
		], [
			'uuid' => data_get($session, 'metadata.uuid'),
			'name' => $customer['name'],
			'password' => Hash::make($password),
			'email_verified_at' => now(),
		]);

		if($session['payment_status'] !== 'paid') {
			return;
		}

		$user->payments()->create([
			'amount_total' => data_get($session, 'amount_total') / 100,
			'status' => 'pending',
		]);

		$this->handleProducts($session['id'], $user);

		$referral = Referral::query()
			->where('code', data_get($session, 'metadata.referral'))
			->first();

		if (!$referral) {
			return;
		}

		$referral->referrers()->create([
			'user_id' => $user->id,
			'referral_id' => $referral->id,
		]);
	}

	private function handleChargeRefunded($event): void {
		$charge = $event->payload['data']['object'];

		$user = User::query()
			->where('email', data_get($charge, 'billing_details.email'))
			->first();

		if (!$user) {
			Log::info('User not found');
			return;
		}

		$user->payments()->create([
			'amount_total' => -data_get($charge, 'amount_refunded') / 100,
			'status' => 'pending',
		]);
	}

	private function handleProducts($session_id, User $user) {
		Stripe::setApiKey(config('cashier.secret'));

		$items = Session::allLineItems($session_id);

		if($items['data']) {
			foreach($items['data'] as $item) {
				switch($item['price']['lookup_key']) {	
					case 'standard':
						$user->standard = true;
						$user->ai_tokens = 20 * 1000000;
						$user->ai_context_limit = 2 * 1000000;
						break;
					case 'pro':
						$user->pro = true;
						break;
					case 'recharge-50m':
						$user->ai_tokens += 50 * 1000000;
						$user->ai_context_limit += 2 * 1000000;
						break;
					case 'recharge-250m':
						$user->ai_tokens += 250 * 1000000;
						$user->ai_context_limit += 5 * 1000000;
						break;
					case 'recharge-500m':
						$user->ai_tokens += 500 * 1000000;
						$user->ai_context_limit += 10 * 1000000;
						break;
				}
			}
		}

		$user->save();
	}
}