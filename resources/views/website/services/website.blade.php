@extends('layouts.website')

@section('title', 'Website Integration | ReplyElf')
@section('description', 'Integrate our AI chatbot directly on your website to provide 24/7 customer support and boost conversions.')

@section('main')
    <!-- Hero Section -->
    <div class="relative pt-16 pb-20 sm:pt-24 sm:pb-32">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="md:max-w-2xl lg:max-w-xl">
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
                        <span class="block text-teal-600">Website Integration</span>
                        <span class="block">Seamless conversational AI for your website</span>
                    </h1>
                    <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg md:mt-5 md:text-xl">
                        Integrate our AI chatbot directly on your website with a simple code snippet. Provide instant support, qualify leads, and boost conversions - all while you sleep.
                    </p>
                    <div class="mt-8 flex gap-4">
                        <flux:button variant="primary" href="{{ route('register') }}" class="px-6 py-3 bg-gradient-to-r from-teal-500 to-green-500">
                            Get Started Free
                        </flux:button>
                        <flux:button variant="ghost" href="#features" class="px-6 py-3">
                            See Features
                        </flux:button>
                    </div>
                </div>
                <div class="mt-12 md:mt-0 md:max-w-2xl lg:max-w-xl">
                    <img src="{{ asset('img/services/website-integration.jpg') }}" alt="Website Integration" class="rounded-lg shadow-xl">
                </div>
            </div>
        </div>
    </div>

    <!-- Service Content -->
    <div class="bg-white py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">
                    Service details coming soon
                </h2>
                <p class="mt-4 text-lg text-gray-500">
                    We're currently building out this page with more detailed information.
                </p>
            </div>
        </div>
    </div>
@endsection 