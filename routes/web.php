<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WaitlistController;
use App\Http\Middleware\CaptureReferral;
use Illuminate\Support\Facades\Route;
use DirectoryTree\ImapEngine\Mailbox;

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/embed.php';

Route::domain(config('app.url'))->group(function () {
    Route::view('/', 'website.index')->name('index');
    
});