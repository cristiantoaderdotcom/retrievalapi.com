@extends('layouts.boilerplate')

@section('body-class', 'bg-[#f1f1f1]')

@section('body')

	Dashboard

	<flux:main container>
		@yield('main')
	</flux:main>
@endsection
