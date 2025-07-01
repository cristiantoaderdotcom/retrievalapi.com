<?php

namespace App\Livewire\Auth\Passwords;

use Livewire\Component;

class Confirm extends Component {
	/** @var string */
	public string $password = '';

	public function store() {
		$this->validate([
			'password' => 'required|current_password',
		]);

		session()->put('auth.password_confirmed_at', time());

		return redirect()->intended(route('app.index'));
	}

	public function render() {
		return view('livewire.auth.passwords.confirm')
			->extends('layouts.auth')
			->section('main');
	}
}