<?php

namespace App\Livewire\Auth\Passwords;

use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Password;

class Email extends Component {
	/** @var string */
	public string $email;

	public function store(): void {
		$this->validate([
			'email' => ['required', 'email'],
		]);

		$status = Password::sendResetLink(
			['email' => $this->email]
		);

		if ($status !== Password::RESET_LINK_SENT) {
			$this->addError('email', __($status));
			return;
		}

		$this->reset();
		Flux::toast(__($status));
	}


	public function render() {
		return view('livewire.auth.passwords.email')
			->extends('layouts.auth')
			->section('main');
	}
}