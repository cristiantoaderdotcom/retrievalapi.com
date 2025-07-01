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
    Route::view('/home', 'website.index')->name('home');

    // Waitlist form submission
    Route::post('/waitlist', [WaitlistController::class, 'store'])
        ->middleware('throttle:waitlist')
        ->name('waitlist.store');

    // Feature pages
    Route::view('/features/chatbot', 'website.features.chatbot')->name('features.chatbot');
    Route::view('/features/analytics', 'website.features.chatbot')->name('features.analytics');
    Route::view('/features/integrations', 'website.features.chatbot')->name('features.integrations');
    Route::view('/features/training', 'website.features.chatbot')->name('features.training');
    Route::view('/features/customization', 'website.features.chatbot')->name('features.customization');
    Route::view('/features/deployment', 'website.features.chatbot')->name('features.deployment');
    
    // Service pages
    Route::view('/services/website', 'website.services.website')->name('services.website');
    Route::view('/services/whatsapp', 'website.services.website')->name('services.whatsapp');
    Route::view('/services/slack', 'website.services.website')->name('services.slack');
    
    // Resource pages
    Route::view('/resources/blog', 'website.resources.chatbot')->name('resources.blog');
    Route::view('/resources/documentation', 'website.resources.chatbot')->name('resources.documentation');
    Route::view('/resources/case-studies', 'website.resources.chatbot')->name('resources.case-studies');
    
    // Primary pages
    Route::view('/pricing', 'website.pricing')->name('pricing');
    Route::view('/about', 'website.about')->name('about');
    Route::view('/contact', 'website.contact')->name('contact');
    Route::view('/careers', 'website.careers')->name('careers');
    Route::view('/demo', 'website.demo')->name('demo');
    
    // Legal pages
    Route::view('/privacy', 'website.privacy')->name('privacy');
    Route::view('/terms', 'website.terms')->name('terms');

    Route::view('/examples', 'website.examples')->name('examples');
    Route::view('/legal/privacy', 'website.privacy')->name('legal.privacy');
    Route::view('/legal/terms-of-service', 'website.terms-of-service')->name('legal.terms-of-service');
    Route::view('/thank-you/{uuid?}', 'website.thank-you')->name('thank-you');
    Route::view('/payment-failed', 'website.payment-failed')->name('payment-failed');

    Route::get('/stripe/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

    Route::view('/offer/{referral}', 'website.index')
            ->middleware(CaptureReferral::class)
            ->name('offers.show');


    Route::get('/fmm-api-prost', function () {
        $response = Http::get('https://replyelf.com/api/test');

        dd($response->json());

        


        
    })->name('test-imap-2');
    


    // Email Inbox Management Routes
    // Make routes available without authentication for local testing
    Route::resource('email-inboxes', \App\Http\Controllers\Admin\EmailInboxController::class);
    Route::post('email-inboxes/{emailInbox}/process', [\App\Http\Controllers\Admin\EmailInboxController::class, 'process'])->name('email-inboxes.process');
    Route::get('processed-emails', [\App\Http\Controllers\Admin\ProcessedEmailController::class, 'index'])->name('processed-emails.index');
    Route::get('processed-emails/{processedEmail}', [\App\Http\Controllers\Admin\ProcessedEmailController::class, 'show'])->name('processed-emails.show');

});