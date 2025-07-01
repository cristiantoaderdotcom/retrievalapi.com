<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class UpdateUserTimestamp {
	/**
	 * Handle the event.
	 */
	public function handle(Login $event): void {
		$event->user->touch();
	}
}
