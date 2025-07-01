@extends('layouts.boilerplate')


@section('title', 'ReplyElf')
@section('description', 'Create intelligent chatbots that boost engagement, generate leads, and provide 24/7 customer support - all without any technical skills')

@section('body')
	<div class="page-container bg-white">
		<div class="page-content">
			<div class="relative z-10">
				<div class="relative">
					<div class="">
						{{-- @include('website._partials.header') --}}
						@yield('main')
					</div>
				</div>
			</div>
			{{-- @include('website._partials.footer') --}}
		</div>
	</div>

	@includeIf('website._partials.cookies')
@endsection