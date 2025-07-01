<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >

	<head>
		<meta charset="utf-8">
		<meta content="width=device-width, initial-scale=1.0, maximum-scale=5.0" name="viewport">
		<meta content="{{ csrf_token() }}" name="csrf-token">

		<title>@yield('title', config('app.name', 'Laravel'))</title>
		<meta content="@yield('keywords')" name="keywords">
		<meta content="@yield('description', 'Create intelligent chatbots that boost engagement, generate leads, and provide 24/7 customer support - all without any technical skills')" name="description">
		<meta content="@yield('title')" name="og:title">
		<meta content="@yield('description')" name="og:description">
		<meta content="@yield('image')" name="og:image">
		<meta content="{{ request()->fullUrl() }}" name="og:url">

		<meta content="noindex, nofollow" name="robots" />

		<link href="https://fonts.googleapis.com" rel="preconnect">
		<link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
		<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

		<link href="{{ asset('assets/favicon/favicon-96x96.png') }}" rel="icon" sizes="96x96" type="image/png" />
		<link href="{{ asset('assets/favicon/favicon.svg') }}" rel="icon" type="image/svg+xml" />
		<link href="{{ asset('assets/favicon/favicon.ico') }}" rel="shortcut icon" />
		<link href="{{ asset('assets/favicon/apple-touch-icon.png') }}" rel="apple-touch-icon" sizes="180x180" />
		<meta content="Iframe AI" name="apple-mobile-web-app-title" />
		<link href="{{ asset('assets/favicon/site.webmanifest') }}" rel="manifest" />

		<link href="https://fonts.googleapis.com" rel="preconnect">
		<link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
		<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

		@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
			@vite(['resources/css/app.css', 'resources/js/app.js'])
		@endif

		@livewireStyles
		

		@stack('styles')


		@if (app()->isProduction())
			@includeif('layouts._partials.pixels')
		@endif
	</head>

	<body class="min-h-screen font-sans antialiased">
		<flux:toast position="top right" />
		@yield('body')

		@livewireScripts
		@fluxScripts
		
		@stack('scripts')
	</body>
</html>