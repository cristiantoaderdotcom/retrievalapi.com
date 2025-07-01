<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider {
	/**
	 * Register any application services.
	 */
	public function register(): void {
		Relation::morphMap([
			'user' => 'App\Models\User',
		]);

		Model::preventLazyLoading(!app()->isProduction());
		//Model::preventAccessingMissingAttributes(!app()->isProduction());
		//Model::preventSilentlyDiscardingAttributes();

		if(app()->isProduction())
			URL::forceScheme('https');
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void {
	}
}
