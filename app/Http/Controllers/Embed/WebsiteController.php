<?php

namespace App\Http\Controllers\Embed;

use App\Http\Controllers\Controller;
use App\Models\Chatbot\ChatbotSetting;
use Illuminate\Http\Request;
use App\Models\Workspace;

class WebsiteController extends Controller {

	public Workspace $workspace;
    

	public array $settings = [];
	
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request, $uuid) {

		$workspace = Workspace::query()
		    ->with('settings')
			->where('uuid', $uuid)
			->firstOrFail();

		if (!$workspace) {
			abort(404);
		}

		$settings = [];

		$general = $workspace->setting('general');
        $business = $workspace->setting('business');
        $styling = $workspace->setting('styling');
        $platform_website = $workspace->setting('platform_website');
		$lead_collector = $workspace->setting('lead_collector');

		$settings = array_merge($general, $business, $styling, $platform_website, $lead_collector);

		//  dd($settings);	

		return view('embed.chatbot.iframe', [
			'workspace' => $workspace,
			'settings' => $settings,
		]);
	}

}
