<?php

namespace App\Livewire\Auth;

use Flux\Flux;
use Illuminate\Http\Request;
use Livewire\Component;

class Verify extends Component {
	public function store(Request $request) {
		if ($request->user()->hasVerifiedEmail()) {
			redirect(route('app.index'));
		}

		$request->user()->sendEmailVerificationNotification();

		$this->dispatch('resent');

		session()->flash('resent');

		Flux::toast(variant: 'success', text: 'A fresh verification link has been sent to your email address.');
	}

	public function render() {
		return view('livewire.auth.verify')
			->extends('layouts.auth')
			->section('main');
	}
}