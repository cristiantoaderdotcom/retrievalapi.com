<?php

use App\Livewire\App\Dashboard;
use App\Livewire\App\Account\Plans;
use Illuminate\Support\Facades\Auth;
use App\Livewire\App\Account\Account;
use Illuminate\Support\Facades\Route;
use App\Livewire\App\Account\Sessions;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\App\Account\Referrals;
use App\Livewire\App\Workspace\WorkspaceShow;
use App\Livewire\App\KnowledgeBase\ImportContent;
use App\Livewire\App\KnowledgeBase\KnowledgeBase;
use App\Livewire\App\KnowledgeBase\ProductCatalog;
use App\Livewire\App\KnowledgeBase\ProductFeeds;
use App\Livewire\App\KnowledgeBase\ManageProduct;
use App\Livewire\App\KnowledgeBase\Playground;
use App\Http\Controllers\WorkspaceSwitcherController;
use App\Livewire\App\Settings\AIPreferences;
use App\Livewire\App\Engagement\Conversations;
use App\Livewire\App\Settings\Workspace as SettingsWorkspace;
use App\Livewire\App\Platforms\Website;
use App\Livewire\App\Platforms\EmailInbox;
use App\Livewire\App\Platforms\Facebook;
use App\Livewire\App\Platforms\Instagram;
use App\Livewire\App\Platforms\Telegram;
use App\Livewire\App\Platforms\FacebookSetup;
use App\Livewire\App\Platforms\InstagramSetup;
use App\Livewire\App\Platforms\TelegramSetup;
use App\Livewire\App\Platforms\Discord;
use App\Livewire\App\Platforms\DiscordSetup;
use App\Livewire\App\Platforms\Api;
use App\Livewire\App\Modules\LeadCollector;
use App\Livewire\App\Settings\AgenticTools;
use App\Livewire\App\AgenticTools\RequestRefunds;
use App\Livewire\App\AgenticTools\Reports;
use App\Livewire\App\AgenticTools\Booking;
use App\Livewire\App\AgenticTools\CustomApiIntegrations;
use App\Livewire\App\AgenticTools\ShoppingAssistant;


Route::middleware(['auth', 'verified'])
	->domain('app.' . parse_url(env('APP_URL'), PHP_URL_HOST))
	->name('app.')
	->group(function () {
		Route::get('/', Dashboard::class)->name('index');

		Route::prefix('account')
			->name('account.')
			->group(function () {
				Route::get('/', Account::class)->name('index');
				Route::get('/sessions', Sessions::class)->name('sessions');
				Route::get('/plans', Plans::class)->name('plans');
				Route::get('/referrals', Referrals::class)->name('referrals');
			});


		Route::prefix('workspace')
			->name('workspace.')
			->group(function () {
				Route::put('/switch/{uuid}', WorkspaceSwitcherController::class)->name('switch');

				Route::prefix('{uuid}')->group(function () {
					Route::get('/', WorkspaceShow::class)->name('show');
					
					Route::prefix('knowledge-base')->name('knowledge-base.')->group(function () {
						Route::get('/import-content', ImportContent::class)->name('import-content');
						Route::get('/knowledge-base', KnowledgeBase::class)->name('knowledge-base');
						Route::get('/product-catalog', ProductCatalog::class)->name('product-catalog');
						Route::get('/product-feeds', ProductFeeds::class)->name('product-feeds');
						Route::get('/product-catalog/{product_id}/manage', ManageProduct::class)->name('manage-product');
						Route::get('/playground', Playground::class)->name('playground');
					});

					Route::prefix('settings')->name('settings.')->group(function () {
						Route::get('/ai-preferences', AIPreferences::class)->name('ai-preferences');
						Route::get('/workspace', SettingsWorkspace::class)->name('workspace');
						Route::get('/agentic-tools', AgenticTools::class)->name('agentic-tools');
					});

					Route::prefix('engagement')->name('engagement.')->group(function () {
						Route::get('/conversations', Conversations::class)->name('conversations');
					});

					Route::prefix('platforms')->name('platforms.')->group(function () {
						Route::get('/website', Website::class)->name('website');
						Route::get('/email-inbox', EmailInbox::class)->name('email-inbox');
						Route::get('/facebook', Facebook::class)->name('facebook');
						Route::get('/facebook/setup', FacebookSetup::class)->name('facebook.setup');
						Route::get('/instagram', Instagram::class)->name('instagram');
						Route::get('/instagram/setup', InstagramSetup::class)->name('instagram.setup');
						Route::get('/telegram', Telegram::class)->name('telegram');
						Route::get('/telegram/setup', TelegramSetup::class)->name('telegram.setup');
						Route::get('/discord', Discord::class)->name('discord');
						Route::get('/discord/setup', DiscordSetup::class)->name('discord.setup');
						Route::get('/api', Api::class)->name('api');
					});

					Route::prefix('agentic-tools')->name('agentic-tools.')->group(function () {
						Route::get('/request-refunds', RequestRefunds::class)->name('request-refunds');
						Route::get('/reports', Reports::class)->name('reports');
						Route::get('/booking', Booking::class)->name('booking');
						Route::get('/shopping-assistant', ShoppingAssistant::class)->name('shopping-assistant');
						Route::get('/custom-api-integrations', CustomApiIntegrations::class)->name('custom-api-integrations');
						Route::get('/custom-api-integrations/create', \App\Livewire\App\AgenticTools\CustomApiIntegrationForm::class)->name('custom-api-integrations.create');
						Route::get('/custom-api-integrations/{integrationId}/edit', \App\Livewire\App\AgenticTools\CustomApiIntegrationForm::class)->name('custom-api-integrations.edit');
					});

					Route::prefix('modules')->name('modules.')->group(function () {
						Route::get('/lead-collector', LeadCollector::class)->name('lead-collector');
					});
				});
			
			});	

		Route::prefix('email-inbox')
			->name('email-inbox.')
			->group(function () {
				// Route::put('/switch/{uuid}', App\Http\Controllers\EmailInboxSwitcherController::class)->name('switch');

				Route::get('/', App\Livewire\App\EmailInbox\Index::class)->name('index');
				Route::get('/create', App\Livewire\App\EmailInbox\Create::class)->name('create');

				Route::prefix('{uuid}')->group(function () {
					Route::get('/', App\Livewire\App\EmailInbox\Show::class)->name('show');
					
					Route::prefix('settings')->name('settings.')->group(function () {
						Route::get('/', App\Livewire\App\EmailInbox\Settings\Index::class)->name('index');
					});
				});
			});

		Route::middleware(['auth', 'verified', 'role:admin'])
			->prefix('admin')
			->name('admin.')
			->group(function () {
				Route::get('/users', UserIndex::class)->name('users.index');

			Route::get('login-using-id/{id}', function($id) {
				Auth::loginUsingId($id);
	
				return redirect()->route('app.index');
			});
		});
	});

