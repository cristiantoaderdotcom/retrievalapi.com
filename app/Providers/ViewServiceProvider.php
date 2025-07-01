<?php

namespace App\Providers;

use App\Models\Review;
use App\Models\Workspace;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot(): void {

		view()->composer(['layouts.app'], function ($view) {
			// Make sure user is authenticated before attempting to load workspaces
			if (auth()->check()) {
				$workspaces = Workspace::query()
					->where('user_id', auth()->user()->id)
					->get();
				
				$view->with('_workspaces', $workspaces);
				
				// If user has workspaces but none is selected in session, automatically select the first one
				if ($workspaces->isNotEmpty() && !session()->has('workspace')) {
					$firstWorkspace = $workspaces->first();
					session()->put('workspace', $firstWorkspace->toArray());
				}
			} else {
				// If user is not authenticated, provide an empty collection
				$view->with('_workspaces', collect([]));
			}
		});

		view()->composer(['layouts.auth'], function ($view) {
			$review = Review::query()
				->inRandomOrder()
				->first();

			$view->with('_review', $review);
		});
	}

	public function register(): void {
	}
}
