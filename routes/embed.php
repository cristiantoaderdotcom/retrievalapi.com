<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Embed\WebsiteController;


Route::domain('embed.' . parse_url(env('APP_URL'), PHP_URL_HOST))
	->name('embed.')
	->group(function () {
		Route::prefix('website')->group(function () {
			Route::get('/{uuid}', [WebsiteController::class, 'index'])->name('website.index');
		});


	});