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

class Account extends Component {
	use HasIcon;

	#[Locked]
	public User $user;

	public string $name = '';
	public string $email = '';
	public ?string $avatar = '';

	public string $current_password = '';
	public string $password = '';
	public string $password_confirmation = '';

	public function mount(Request $request): void {
		$this->user = $request->user();

		$this->name = $this->user->name;
		$this->email = $this->user->email;
		$this->avatar = $this->user->avatar;
	}

	public function profile(): void {
		$this->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|string|email|max:255|unique:users,email,' . $this->user->id,
		]);

		$this->handleIconUpload('avatar');

		$this->user->fill([
			'name' => $this->name,
			'email' => $this->email,
			'avatar' => $this->avatar,
		]);

		if ($this->user->isDirty('email'))
			$this->user->email_verified_at = null;

		$this->user->save();

		if ($this->user->wasChanged('email')) {
			$this->user->sendEmailVerificationNotification();
		}

		Flux::toast(variant: 'success', text: 'Profile updated successfully.');
	}

	/**
	 * @throws ValidationException
	 */
	public function updatePassword(): void {
		$this->validate([
			'current_password' => ['required', 'string', 'current_password'],
			'password' => ['required', 'string', Password::defaults(), 'confirmed'],
		]);

		$this->user->update([
			'password' => Hash::make($this->password),
		]);

		$this->reset('current_password', 'password', 'password_confirmation');

		Flux::toast(variant: 'success', text: 'Password updated successfully.');
	}

	public function render() {
		return view('livewire.app.account.account')
			->extends('layouts.app')
			->section('main');
	}

	protected function getDefaultIconSize(): int {
		return 64;
	}
}
