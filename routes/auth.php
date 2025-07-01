<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Middleware\CapturePlan;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Passwords\Confirm;
use App\Livewire\Auth\Passwords\Email;
use App\Livewire\Auth\Passwords\Reset;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\Verify;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::domain('app.' . env('APP_URL'))->group(function () {
	Route::middleware(['auth', 'verified', 'password.confirm'])
		->group(function () {
			
		});

	Route::middleware(['guest'])->group(function () {
		Route::get('login/{uuid?}', Login::class)
			->name('login');

		Route::middleware(['throttle:2,160'])->group(function () {
			Route::get('get-started', Register::class)
				// ->middleware(CapturePlan::class)
				->name('register');
		});

		Route::get('auth/{provider}', [SocialiteController::class, 'create'])->name('socialite.redirect');
		Route::get('auth/{provider}/callback', [SocialiteController::class, 'store'])->name('socialite.callback');
	});

	Route::get('password/reset', Email::class)
		->name('password.request');

	Route::get('password/reset/{token}', Reset::class)
		->name('password.reset');

	Route::middleware('auth')->group(function () {
		Route::get('email/verify', Verify::class)
			->middleware('throttle:6,1')
			->name('verification.notice');

		Route::get('password/confirm', Confirm::class)
			->name('password.confirm');
	});

	Route::middleware('auth')->group(function () {
		Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
			->middleware('signed')
			->name('verification.verify');

		Route::post('logout', LogoutController::class)
			->name('logout');
	});
});
