@extends('layouts.boilerplate')
@section('body')
	<div class="relative flex min-h-screen" x-data="{ collapsed: localStorage.getItem('navbar') === 'true' }">
		@includeIf('layouts._partials.app.navbar')
		<div class="relative z-30 flex min-w-0 flex-1 flex-col p-3">
			<div class="flex flex-col h-full rounded-xl px-4 lg:px-10 space-y-6">
				@includeIf('layouts._partials.app.topbar')

				<div class="">
					@yield('main')
				</div>

				<div class="text-center mt-auto! pt-16 pb-6 text-xs">
					Copyrights &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved
				</div>
			</div>
		</div>
	</div>
	@fluxAppearance
@endsection

@if(!empty($file_contents))
    {{-- Add preconnect links in the head section --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@endif
