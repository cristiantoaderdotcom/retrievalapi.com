<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\UserReferral;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component {
	public string $email = '';

	public string $password = '';

	public bool $remember = true;

	public ?string $uuid = null;

	public function store() {
		$this->validate([
			'email' => ['required', 'email'],
			'password' => ['required'],
			'remember' => ['boolean'],
		]);

		try {
			$this->ensureIsNotRateLimited();
		} catch (ValidationException $e) {
			$this->addError('email', $e->getMessage());
			return false;
		}

		if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
			RateLimiter::hit($this->throttleKey());

			$this->addError('email', trans('auth.failed'));
			return false;
		}

		RateLimiter::clear($this->throttleKey());

		return redirect()->intended(route('app.index'));
	}

	/**
	 * @throws ValidationException
	 */
	public function ensureIsNotRateLimited(): void {
		if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5))
			return;

		event(new Lockout(request()));

		$seconds = RateLimiter::availableIn($this->throttleKey());

		throw ValidationException::withMessages([
			'email' => trans('auth.throttle', [
				'seconds' => $seconds,
				'minutes' => ceil($seconds / 60),
			]),
		]);
	}

	public function throttleKey(): string {
		return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
	}

	public function mount($uuid = null) {
		$this->uuid = $uuid;

		try {
			if (!empty($this->uuid)) {
				$this->ensureIsNotRateLimited();

				$user = User::query()
					->where('uuid', $this->uuid)
					->first();

				if (!$user) {
					RateLimiter::hit($this->throttleKey());

					$this->addError('email', trans('auth.failed'));
					return false;
				}

				Auth::loginUsingId($user->id, true);
				$this->redirect(route('app.account.plans'));
			}
		} catch (ValidationException $e) {
			abort(429);
			return false;
		}
	}

	public function render() {
		return view('livewire.auth.login')
			->extends('layouts.auth')
			->section('main');
	}
}
