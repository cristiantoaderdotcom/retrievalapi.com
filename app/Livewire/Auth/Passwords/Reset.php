<?php

namespace App\Livewire\Auth\Passwords;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules;

class Reset extends Component {
	public string $token;

	public string $email;

	public string $password;

	public string $password_confirmation;

	public function mount($token): void {
		$this->email = request()->query('email', '');
		$this->token = $token;
	}

	public function store() {
		$this->validate([
			'token' => ['required'],
			'email' => ['required', 'email'],
			'password' => ['required', 'confirmed', Rules\Password::defaults()],
		]);

		$status = Password::reset([
			'token' => $this->token,
			'email' => $this->email,
			'password' => $this->password,
			'password_confirmation' => $this->password_confirmation
		], function ($user) {
			$user->forceFill([
				'password' => Hash::make($this->password),
				'remember_token' => Str::random(60),
			])->save();

			Auth::login($user);

			event(new PasswordReset($user));
		});

		Log::info(__($status));

		if ($status !== Password::PASSWORD_RESET) {
			$this->addError('email', __($status));
			return false;
		}

		return redirect()->route('app.index');
	}

	public function render() {
		return view('livewire.auth.passwords.reset')
			->extends('layouts.auth')
			->section('main');
	}
}