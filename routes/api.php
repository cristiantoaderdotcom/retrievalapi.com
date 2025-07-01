<?php

use App\Http\Api\Controllers\Response\WebsiteController;
use App\Http\Api\Webhook\FacebookController;
use App\Http\Api\Webhook\InstagramController;
use App\Http\Api\Webhook\TelegramController;
use App\Http\Api\Webhook\DiscordController;
use App\Http\Api\Controllers\Response\MessageController;
use Illuminate\Support\Facades\Route;

Route::prefix('website')
	->group(function () {
		Route::post('/session', [WebsiteController::class, 'session'])->name('api.website.session');
		Route::post('/message', [WebsiteController::class, 'message'])->name('api.website.message');
		Route::post('/lead', [WebsiteController::class, 'lead'])->name('api.website.lead');
		Route::post('/dislike', [WebsiteController::class, 'dislikeMessage'])->name('api.website.dislike');
	});

Route::prefix('facebook')
	->group(function () {
		Route::post('/webhook/{page_uuid}', [FacebookController::class, 'webhook'])->name('api.facebook.webhook');
		Route::get('/webhook/{page_uuid}', [FacebookController::class, 'verify'])->name('api.facebook.verify');
	});

Route::prefix('instagram')
	->group(function () {
		Route::post('/webhook/{page_uuid}', [InstagramController::class, 'webhook'])->name('api.instagram.webhook');
		Route::get('/webhook/{page_uuid}', [InstagramController::class, 'verify'])->name('api.instagram.verify');
	});

Route::prefix('telegram')
	->group(function () {
		Route::post('/webhook/{bot_uuid}', [TelegramController::class, 'webhook'])->name('api.telegram.webhook');
	});

Route::prefix('discord')
	->group(function () {
		Route::post('/webhook', [DiscordController::class, 'webhook'])->name('api.discord.webhook');
	});

// API for workspace integrations
Route::prefix('v1')
	->group(function () {
		Route::post('/message', [MessageController::class, 'process'])->name('api.v1.message');
	});

Route::get('/test', function () {
	return response()->json([
		'message' => 'Cristian is the best developer in the world'
	]);
});


Route::get('/test-order/{order_id}', function ($order_id) {
	return response()->json([
		'order' => [
			'id' => $order_id,
			'name' => 'Order ' . $order_id,
			'price' => 100,
			'status' => 'pending',
			'created_at' => '2021-01-01',
			'updated_at' => '2021-01-01',
			'customer' => [
				'id' => 1,
				'name' => 'John Doe',
				'email' => 'john.doe@example.com',
				'phone' => '+1234567890',
				'address' => '123 Main St, Anytown, USA',
				'city' => 'Anytown',
				'state' => 'CA',
				'zip' => '12345',
			],
			'products' => [
				[
					'id' => 1,
					'name' => 'Product ' . $order_id,
					'price' => 100
				],
				[
					'id' => 2,
					'name' => 'Product ' . $order_id,
					'price' => 200
				]
			]
		]
	]);
});