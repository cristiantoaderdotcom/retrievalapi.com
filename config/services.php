<?php

return [

	/*
	  |--------------------------------------------------------------------------
	  | Third Party Services
	  |--------------------------------------------------------------------------
	  |
	  | This file is for storing the credentials for third party services such
	  | as Mailgun, Postmark, AWS and more. This file provides the de facto
	  | location for this type of information, allowing packages to have
	  | a conventional file to locate the various service credentials.
	  |
	  */

	'postmark' => [
		'token' => env('POSTMARK_TOKEN'),
	],

	'ses' => [
		'key' => env('AWS_ACCESS_KEY_ID'),
		'secret' => env('AWS_SECRET_ACCESS_KEY'),
		'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
	],

	'resend' => [
		'key' => env('RESEND_KEY'),
	],

	'slack' => [
		'notifications' => [
			'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
			'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
		],
	],

	'openai' => [
		'key' => env('OPENAI_API_KEY'),
		'organization' => env('OPENAI_ORGANIZATION'),
	],

	'google' => [
		'client_id' => env('GOOGLE_CLIENT_ID'),
		'client_secret' => env('GOOGLE_CLIENT_SECRET'),
		'redirect' => env('GOOGLE_REDIRECT_URL'),
	],

	'poppler' => [
		'bin' => env('POPPLER_BIN'),
	],

	'groq' => [
		'api_key' => env('GROQ_API_KEY'),
	],

	'gemini' => [
		'api_key' => env('GEMINI_API_KEY'),
	],

	'facebook' => [
		'page_token' => env('FACEBOOK_PAGE_TOKEN'),
		'verify_token' => env('FACEBOOK_VERIFY_TOKEN'),
	],

	'discord' => [
		'application_id' => env('DISCORD_APPLICATION_ID', '1369918518459498566'),
		'token' => env('DISCORD_BOT_TOKEN'),
		'public_key' => env('DISCORD_PUBLIC_KEY'),
	],

	'kit' => [
		'api_key' => env('KIT_API_KEY'),
	],

	'rapidapi' => [
		'key' => env('RAPIDAPI_KEY'),
	],

];
