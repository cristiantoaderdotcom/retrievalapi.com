<?php

namespace App\Livewire\App\Account;

use App\Models\Session;
use Flux\Flux;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use Livewire\Component;

class Sessions extends Component {
	/**
	 * The user's current password.
	 *
	 * @var string
	 */
	public string $password = '';

	/**
	 * Log out from other browser sessions.
	 *
	 * @return void
	 * @throws AuthenticationException
	 */
	public function logout() {
		if (config('session.driver') !== 'database') {
			return;
		}

		if (!Hash::check($this->password, Auth::user()->password)) {
			$this->addError('password_confirmation', __('This password does not match our records.'));
			return;
		}

		$guard = Auth::guard('web');

		$guard->logoutOtherDevices($this->password);

		$this->deleteOtherSessionRecords();

		request()->session()->put([
			'password_hash_' . Auth::getDefaultDriver() => Auth::user()->getAuthPassword(),
		]);

		$this->modal('logout')->close();
		Flux::toast(variant: 'success', text: 'You have been logged out of other browser sessions.');
	}

	/**
	 * Delete the other browser session records from storage.
	 *
	 * @return void
	 */
	protected function deleteOtherSessionRecords(): void {
		if (config('session.driver') !== 'database') {
			return;
		}

		Session::query()
			->where('user_id', Auth::user()->getAuthIdentifier())
			->where('id', '!=', request()->session()->getId())
			->delete();
	}

	/**
	 * Get the current sessions.
	 *
	 * @return Collection
	 */
	public function getSessionsProperty(): Collection {
		if (config('session.driver') !== 'database') {
			return collect();
		}

		return Session::query()
			->where('user_id', Auth::user()->getAuthIdentifier())
			->orderBy('last_activity', 'desc')
			->get()
			->map(function ($session) {
				return (object) [
					'agent' => $this->createAgent($session),
					'ip_address' => $session->ip_address,
					'is_current_device' => $session->id === request()->session()->getId(),
					'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
				];
			});
	}

	/**
	 * Create a new agent instance from the given session.
	 *
	 * @param mixed $session
	 */
	protected function createAgent(mixed $session) {
		return tap(new Agent(), fn($agent) => $agent->setUserAgent($session->user_agent));
	}

	public function render() {
		return view('livewire.app.account.sessions')
			->extends('layouts.app')
			->section('main');
	}
}
