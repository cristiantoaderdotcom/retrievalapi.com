@extends('layouts.website')

@section('title', 'AI Chatbot | ReplyElf')
@section('description', 'Create intelligent AI chatbots that understand context, answer questions accurately, and learn from every interaction.')

@section('main')
    <!-- Hero Section -->
    <div class="relative pt-16 pb-20 sm:pt-24 sm:pb-32">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="md:max-w-2xl lg:max-w-xl">
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
                        <span class="block text-blue-600">AI Chatbot</span>
                        <span class="block">Intelligent conversations for your business</span>
                    </h1>
                    <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg md:mt-5 md:text-xl">
                        Our AI-powered chatbot understands context, answers questions accurately, and learns from every interaction to provide exceptional customer experiences.
                    </p>
                    <div class="mt-8 flex gap-4">
                        <flux:button variant="primary" href="{{ route('register') }}" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600">
                            Start Free Trial
                        </flux:button>
                        <flux:button variant="ghost" href="{{ route('contact') }}" class="px-6 py-3">
                            Contact Sales
                        </flux:button>
                    </div>
                </div>
                <div class="mt-12 md:mt-0 md:max-w-2xl lg:max-w-xl">
                    <img src="{{ asset('img/features/chatbot-hero.jpg') }}" alt="AI Chatbot" class="rounded-lg shadow-xl">
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Content -->
    <div class="bg-white py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">
                    Feature details coming soon
                </h2>
                <p class="mt-4 text-lg text-gray-500">
                    We're currently building out this page with more detailed information.
                </p>
            </div>
        </div>
    </div>
@endsection 