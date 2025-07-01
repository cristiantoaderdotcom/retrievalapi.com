<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller {
	public function create($provider): RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse {
		return Socialite::driver($provider)->redirect();
	}

	public function store($provider): RedirectResponse|Checkout {
		try {
			$socialite = Socialite::driver($provider)->user();

			$user = User::query()
				->firstOrCreate([
					'email' => $socialite->getEmail(),
				],[
					'name' => $socialite->getName(),
					'password' => Hash::make($socialite->getName() . '@' . $socialite->getId()),
					'email_verified_at' => now(),
				]);

			if($user->wasRecentlyCreated) {
				event(new Registered($user));
			}

			Auth::login($user, true);
		} catch (Exception $e) {
			Log::error('Social login error', ['error' => $e->getMessage()]);
		}

		return redirect()->intended(route('app.index', absolute: false));
	}
}
