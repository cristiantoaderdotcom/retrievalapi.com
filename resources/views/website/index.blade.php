@extends('layouts.website')

@section('main')
    <!-- Hero Section with Enhanced Design -->
    <div class="relative overflow-hidden bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 pt-15 pb-20">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239C92AC" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-40"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
            <div class="max-w-5xl mx-auto text-center">

                <!-- logo -->
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="ReplyElf Logo" class="h-20">
                </div>

                {{-- <!-- Announcement Badge -->
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/80 backdrop-blur-sm border border-green-200 text-green-700 text-sm font-medium mb-8 shadow-lg">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                    Coming Soon - Join the AI Revolution
                </div>
                 --}}
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-gray-900 leading-tight mb-8">
                    The Future of 
                    <span class="relative">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600">
                            AI Customer Engagement
                        </span>
                        <div class="absolute -bottom-2 left-0 right-0 h-1 bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 rounded-full opacity-30"></div>
                    </span>
                    <br class="hidden sm:block">is Coming Soon
                </h1>
                
                <p class="text-xl sm:text-2xl text-gray-600 max-w-4xl mx-auto mb-12 leading-relaxed">
                    Be among the first to experience intelligent AI chatbots that revolutionize customer engagement. 
                    Join our exclusive waitlist and secure your early access.
                </p>
                
                <div class="flex flex-wrap justify-center gap-4 text-sm">
                    <div class="flex items-center px-4 py-2 bg-white/70 backdrop-blur-sm rounded-full border border-gray-200">
                        <span class="text-2xl mr-2">🚀</span>
                        <span class="font-medium text-gray-700">Launching Q3 2025</span>
                    </div>
                    <div class="flex items-center px-4 py-2 bg-white/70 backdrop-blur-sm rounded-full border border-gray-200">
                        <span class="text-2xl mr-2">🎁</span>
                        <span class="font-medium text-gray-700">25% Early Bird Discount</span>
                    </div>
                    <div class="flex items-center px-4 py-2 bg-white/70 backdrop-blur-sm rounded-full border border-gray-200">
                        <span class="text-2xl mr-2">⚡</span>
                        <span class="font-medium text-gray-700">Limited Spots Available</span>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- <!-- Hero Image with Enhanced Design -->
        <div class="mt-20 mx-auto max-w-7xl px-4 sm:px-6">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-green-600 to-emerald-600 rounded-3xl blur-xl opacity-20 scale-105"></div>
                <div class="relative bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-200">
                    <img src="{{ asset('img/dashboard-preview.jpg') }}" alt="Dashboard Preview" class="w-full">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/30 to-transparent"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="bg-white/95 backdrop-blur-md rounded-2xl px-8 py-4 shadow-xl border border-white/20">
                            <p class="text-gray-900 font-semibold text-lg">Coming Soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    
    <!-- Waitlist Form Section with Modern Design -->
    <div class="relative py-24 bg-white">
        <!-- Background Elements -->
        <div class="absolute inset-0 bg-gradient-to-b from-gray-50/50 to-white"></div>
        {{-- <div class="absolute top-0 left-1/4 w-72 h-72 bg-green-400 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-pulse"></div>
        <div class="absolute top-0 right-1/4 w-72 h-72 bg-teal-400 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-pulse" style="animation-delay: 2s;"></div>
         --}}
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
                    Join Our <span class="bg-clip-text text-transparent bg-gradient-to-r from-green-600 to-teal-600">Exclusive Waitlist</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Get priority access, special launch pricing, and help shape the future of AI customer support. 
                    Tell us about your business needs!
                </p>
            </div>

            @if(session('success'))
                <div class="mb-12 max-w-2xl mx-auto">
                    <div class="rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 p-6 border border-emerald-200 shadow-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center">
                                    <flux:icon name="check" class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-emerald-900">Welcome to the Waitlist!</h3>
                                <p class="text-emerald-700 mt-1">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->has('rate_limit'))
                <div class="mb-12 max-w-2xl mx-auto">
                    <div class="rounded-2xl bg-gradient-to-r from-red-50 to-pink-50 p-6 border border-red-200 shadow-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                                    <flux:icon name="exclamation-triangle" class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-red-900">Submission Limit Reached</h3>
                                <p class="text-red-700 mt-1">{{ $errors->first('rate_limit') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Form Container with Glass Effect -->
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-8 sm:p-12 lg:p-16 shadow-2xl border border-white/20">
                    <form action="{{ route('waitlist.store') }}" method="POST" class="space-y-10" id="waitlist-form">
                        @csrf
                        
                        <!-- Personal Information Section -->
                        <div class="space-y-8">
                            <div class="flex items-center space-x-3 mb-8">
                                <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-teal-500 rounded-full flex items-center justify-center text-white font-bold text-sm">1</div>
                                <h3 class="text-2xl font-bold text-gray-900">Personal Information</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <div class="space-y-3">
                                    <label for="name" class="block text-base font-semibold text-gray-700">
                                        Your Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                        class="block w-full px-6 py-4 text-lg rounded-2xl border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-500/20 transition-all duration-200 bg-white/50 backdrop-blur-sm @error('name') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                        placeholder="Enter your full name">
                                    @error('name')
                                        <p class="text-red-600 text-sm font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-3">
                                    <label for="email" class="block text-base font-semibold text-gray-700">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                        class="block w-full px-6 py-4 text-lg rounded-2xl border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-500/20 transition-all duration-200 bg-white/50 backdrop-blur-sm @error('email') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                        placeholder="your@email.com">
                                    @error('email')
                                        <p class="text-red-600 text-sm font-medium">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Business Information Section -->
                        <div class="space-y-8">
                            <div class="flex items-center space-x-3 mb-8">
                                <div class="w-8 h-8 bg-gradient-to-r from-teal-500 to-slate-500 rounded-full flex items-center justify-center text-white font-bold text-sm">2</div>
                                <h3 class="text-2xl font-bold text-gray-900">Business Information</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <div class="space-y-3">
                                    <label for="business_name" class="block text-base font-semibold text-gray-700">
                                        Business Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="business_name" id="business_name" value="{{ old('business_name') }}" required
                                        class="block w-full px-6 py-4 text-lg rounded-2xl border-2 border-gray-200 shadow-sm focus:border-teal-500 focus:ring-4 focus:ring-teal-500/20 transition-all duration-200 bg-white/50 backdrop-blur-sm @error('business_name') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                        placeholder="Your business name">
                                    @error('business_name')
                                        <p class="text-red-600 text-sm font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-3">
                                    <label for="website" class="block text-base font-semibold text-gray-700">
                                        Website URL
                                    </label>
                                    <input type="url" name="website" id="website" value="{{ old('website') }}" 
                                        class="block w-full px-6 py-4 text-lg rounded-2xl border-2 border-gray-200 shadow-sm focus:border-teal-500 focus:ring-4 focus:ring-teal-500/20 transition-all duration-200 bg-white/50 backdrop-blur-sm @error('website') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                        placeholder="https://yourwebsite.com">
                                    @error('website')
                                        <p class="text-red-600 text-sm font-medium">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="space-y-3">
                                <label for="niche" class="block text-base font-semibold text-gray-700">
                                    Your Business Niche/Industry <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="niche" id="niche" value="{{ old('niche') }}" required
                                    class="block w-full px-6 py-4 text-lg rounded-2xl border-2 border-gray-200 shadow-sm focus:border-teal-500 focus:ring-4 focus:ring-teal-500/20 transition-all duration-200 bg-white/50 backdrop-blur-sm @error('niche') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                    placeholder="e.g., E-commerce, SaaS, Healthcare, Real Estate, etc.">
                                @error('niche')
                                    <p class="text-red-600 text-sm font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Platform Selection Section -->
                        <div class="space-y-8">
                            <div class="flex items-center space-x-3 mb-8">
                                <div class="w-8 h-8 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full flex items-center justify-center text-white font-bold text-sm">3</div>
                                <h3 class="text-2xl font-bold text-gray-900">Platform Integration</h3>
                            </div>
                            
                            <div class="space-y-4">
                                <label class="block text-base font-semibold text-gray-700 mb-6">
                                    Which platforms would you like to integrate? <span class="text-gray-500 font-normal">(Select all that apply)</span>
                                </label>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @php
                                        $platforms = [
                                            'website' => ['label' => 'Website Chat Widget', 'icon' => 'globe-alt'],
                                            'api' => ['label' => 'API Integration', 'icon' => 'code-bracket'],
                                            'email' => ['label' => 'Email Support', 'icon' => 'envelope'],
                                            'facebook' => ['label' => 'Facebook Messenger', 'icon' => 'chat-bubble-oval-left-ellipsis'],
                                            'instagram' => ['label' => 'Instagram DMs', 'icon' => 'camera'],
                                            'telegram' => ['label' => 'Telegram', 'icon' => 'chat-bubble-oval-left-ellipsis'],
                                            'discord' => ['label' => 'Discord', 'icon' => 'chat-bubble-oval-left-ellipsis'],
                                            'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'device-phone-mobile']
                                        ];
                                        $oldPlatforms = old('platforms', []);
                                    @endphp
                                    
                                    @foreach($platforms as $key => $platform)
                                        <label class="group relative cursor-pointer">
                                            <input type="checkbox" name="platforms[]" value="{{ $key }}" 
                                                class="peer sr-only platform-checkbox" @if(in_array($key, $oldPlatforms)) checked @endif>
                                            
                                            <div class="relative flex items-center p-5 bg-white border-2 border-gray-200 rounded-2xl shadow-sm transition-all duration-300 hover:shadow-md hover:border-gray-300 group-hover:scale-102 peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-lg peer-checked:scale-105">
                                                <!-- Checkmark Circle -->
                                                <div class="absolute top-3 right-3 w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center transition-all duration-200 peer-checked:border-green-500 peer-checked:bg-green-500">
                                                    <svg class="h-4 w-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                                
                                                <div class="flex items-center space-x-4 w-full">
                                                    <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center group-hover:bg-gray-200 transition-colors duration-200 peer-checked:bg-green-100">
                                                        <flux:icon name="{{ $platform['icon'] }}" class="h-6 w-6 text-gray-600 peer-checked:text-green-600" />
                                                    </div>
                                                    <span class="text-lg font-semibold text-gray-900 peer-checked:text-green-900">
                                                        {{ $platform['label'] }}
                                                    </span>
                                                </div>
                                                
                                                <!-- Selected overlay -->
                                                <div class="absolute inset-0 rounded-2xl bg-green-500/10 opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('platforms')
                                    <p class="text-red-600 text-sm font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Features Section -->
                        <div class="space-y-8">
                            <div class="flex items-center space-x-3 mb-8">
                                <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center text-white font-bold text-sm">4</div>
                                <h3 class="text-2xl font-bold text-gray-900">Your Needs</h3>
                            </div>
                            
                            <div class="space-y-3">
                                <label for="desired_features" class="block text-base font-semibold text-gray-700">
                                    What features are you most interested in?
                                </label>
                                <textarea name="desired_features" id="desired_features" rows="6" 
                                    class="block w-full px-6 py-4 text-lg rounded-2xl border-2 border-gray-200 shadow-sm focus:border-orange-500 focus:ring-4 focus:ring-orange-500/20 transition-all duration-200 bg-white/50 backdrop-blur-sm resize-none @error('desired_features') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                    placeholder="Tell us about the features you need: AI-powered responses, lead generation, 24/7 support, analytics, integrations, custom workflows, etc.">{{ old('desired_features') }}</textarea>
                                @error('desired_features')
                                    <p class="text-red-600 text-sm font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center pt-8">
                            <button type="submit" 
                                class="group relative inline-flex items-center px-12 py-5 text-xl font-bold text-white bg-gradient-to-r from-green-600 via-teal-600 to-slate-600 rounded-2xl shadow-xl hover:shadow-2xl focus:outline-none focus:ring-4 focus:ring-green-500/50 transition-all duration-300 transform hover:-translate-y-2 hover:scale-105 overflow-hidden">
                                
                                <!-- Animated background -->
                                <div class="absolute inset-0 bg-gradient-to-r from-green-600 via-teal-600 to-slate-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-600 via-teal-600 to-green-600 group-hover:animate-pulse"></div>
                                
                                <div class="relative flex items-center">
                                    <flux:icon name="sparkles" class="h-6 w-6 mr-3 group-hover:animate-spin" />
                                    Join the Waitlist
                                    <flux:icon name="arrow-right" class="h-6 w-6 ml-3 group-hover:translate-x-1 transition-transform duration-200" />
                                </div>
                            </button>
                            
                            <p class="mt-6 text-gray-600 text-lg">
                                By joining, you'll get <span class="font-semibold text-green-600">early access</span> and <span class="font-semibold text-teal-600">exclusive launch pricing</span>
                            </p>
                            
                            <div class="mt-4 flex items-center justify-center space-x-2 text-sm text-gray-500">
                                <flux:icon name="shield-check" class="h-5 w-5 text-emerald-500" />
                                <span>Your information is 100% secure and will never be shared</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Benefits Section with Enhanced Design -->
    <div class="relative py-24 bg-gradient-to-b from-gray-50 to-white overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute top-0 left-0 w-96 h-96 bg-green-400 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-teal-400 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-pulse" style="animation-delay: 3s;"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
                    Why Join Our <span class="bg-clip-text text-transparent bg-gradient-to-r from-green-600 to-teal-600">Waitlist?</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Get exclusive benefits as an early adopter and help shape the future of AI customer support
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                    $benefits = [
                        ['icon' => 'bolt', 'title' => 'Early Access', 'description' => 'Be among the first to experience our revolutionary AI chatbot platform before public launch.', 'color' => 'green'],
                        ['icon' => 'tag', 'title' => '25% Launch Discount', 'description' => 'Lock in exclusive pricing with up to 25% off our regular rates for your first year.', 'color' => 'teal'],
                        ['icon' => 'user-plus', 'title' => 'VIP Support', 'description' => 'Get priority customer support and direct access to our development team.', 'color' => 'emerald'],
                        ['icon' => 'gift', 'title' => 'Bonus Features', 'description' => 'Unlock premium features and integrations at no extra cost during your first year.', 'color' => 'orange'],
                        ['icon' => 'heart', 'title' => 'Shape the Product', 'description' => 'Your feedback will directly influence features and improvements before release.', 'color' => 'slate'],
                        ['icon' => 'academic-cap', 'title' => 'Free Training', 'description' => 'Get complimentary onboarding and training sessions to maximize potential.', 'color' => 'teal']
                    ];
                @endphp
                
                @foreach($benefits as $benefit)
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-{{ $benefit['color'] }}-600 to-{{ $benefit['color'] }}-400 rounded-3xl blur opacity-25 group-hover:opacity-40 transition-opacity duration-300"></div>
                        <div class="relative bg-white rounded-3xl p-8 shadow-xl border border-gray-100 group-hover:shadow-2xl group-hover:-translate-y-2 transition-all duration-300">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-r from-{{ $benefit['color'] }}-500 to-{{ $benefit['color'] }}-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <flux:icon name="{{ $benefit['icon'] }}" class="h-7 w-7 text-white" />
                                </div>
                                <h3 class="ml-4 text-xl font-bold text-gray-900">{{ $benefit['title'] }}</h3>
                            </div>
                            <p class="text-gray-600 leading-relaxed">{{ $benefit['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Coming Soon Features with Enhanced Design -->
    <div class="relative py-24 bg-white overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239C92AC" fill-opacity="0.03"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-40"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
                    What's <span class="bg-clip-text text-transparent bg-gradient-to-r from-green-600 to-teal-600">Coming</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Revolutionary features that will transform your customer engagement forever
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                    $features = [
                        ['icon' => 'chat-bubble-left-right', 'title' => 'AI-Powered Conversations', 'description' => 'Advanced natural language processing that understands context and provides human-like responses.', 'badge' => 'Core Feature', 'gradient' => 'from-green-50 to-teal-50', 'border' => 'border-green-100', 'badge-color' => 'bg-green-100 text-green-800'],
                        ['icon' => 'cog', 'title' => 'Smart Automation', 'description' => 'Automatically handle customer inquiries, route conversations, and escalate when needed.', 'badge' => 'Automation', 'gradient' => 'from-teal-50 to-slate-50', 'border' => 'border-teal-100', 'badge-color' => 'bg-teal-100 text-teal-800'],
                        ['icon' => 'device-phone-mobile', 'title' => 'Omnichannel Support', 'description' => 'Deploy across websites, social media, messaging apps, and more with unified management.', 'badge' => 'Multi-Platform', 'gradient' => 'from-emerald-50 to-teal-50', 'border' => 'border-emerald-100', 'badge-color' => 'bg-emerald-100 text-emerald-800'],
                        ['icon' => 'chart-bar', 'title' => 'Advanced Analytics', 'description' => 'Deep insights into customer behavior, conversation patterns, and performance metrics.', 'badge' => 'Analytics', 'gradient' => 'from-orange-50 to-yellow-50', 'border' => 'border-orange-100', 'badge-color' => 'bg-orange-100 text-orange-800'],
                        ['icon' => 'puzzle-piece', 'title' => 'Seamless Integrations', 'description' => 'Connect with your favorite platforms: Facebook, Instagram, WhatsApp, Telegram, Discord, and more.', 'badge' => 'Integration', 'gradient' => 'from-yellow-50 to-orange-50', 'border' => 'border-yellow-100', 'badge-color' => 'bg-yellow-100 text-yellow-800'],
                        ['icon' => 'wrench-screwdriver', 'title' => 'Fast and Easy Setup', 'description' => 'Easy to use and integrate with your existing website or application.', 'badge' => 'No-Code', 'gradient' => 'from-slate-50 to-gray-50', 'border' => 'border-slate-100', 'badge-color' => 'bg-slate-100 text-slate-800']
                    ];
                @endphp
                
                @foreach($features as $feature)
                    <div class="group relative bg-gradient-to-br {{ $feature['gradient'] }} rounded-3xl p-8 border {{ $feature['border'] }} hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                        <div class="absolute top-6 right-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $feature['badge-color'] }}">
                                {{ $feature['badge'] }}
                            </span>
                        </div>
                        <flux:icon name="{{ $feature['icon'] }}" class="h-12 w-12 text-gray-700 mb-6 group-hover:scale-110 transition-transform duration-200" />
                        <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $feature['title'] }}</h3>
                        <p class="text-gray-600 leading-relaxed">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Final CTA with Enhanced Design -->
    <div class="relative py-24 bg-gradient-to-r from-green-600 via-teal-600 to-slate-600 overflow-hidden">
        <!-- Background Animation -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] animate-pulse"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl sm:text-5xl font-bold text-white mb-6">
                Don't Miss Out on the <span class="text-yellow-300">AI Revolution</span>
            </h2>
            <p class="text-xl text-green-100 mb-8 max-w-3xl mx-auto">
                Join ReplyElf's waitlist to be among the first to experience our revolutionary AI chatbot platform before public launch.
            </p>
            
            <!-- Stats -->
            <div class="flex flex-wrap justify-center gap-8 mb-12">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/20">
                    <div class="text-2xl font-bold text-white">Help Shape the Future</div>
                    <div class="text-green-100">Your Feedback Matters, help us build all the features you need</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/20">
                    <div class="text-2xl font-bold text-white">Q3 2025</div>
                    <div class="text-green-100">Launch Date</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/20">
                    <div class="text-2xl font-bold text-white">25% OFF</div>
                    <div class="text-green-100">Early Bird Pricing</div>
                </div>
            </div>
            
            <a href="#waitlist-form" class="inline-flex items-center px-12 py-5 text-xl font-bold text-green-600 bg-white rounded-2xl shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group">
                <flux:icon name="arrow-up" class="h-6 w-6 mr-3 group-hover:-translate-y-1 transition-transform duration-200" />
                Join Waitlist Above
                <flux:icon name="sparkles" class="h-6 w-6 ml-3 group-hover:animate-spin" />
            </a>
        </div>
    </div>

    <script>
        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Handle checkbox visual feedback
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.platform-checkbox');
            
            checkboxes.forEach(checkbox => {
                const container = checkbox.closest('label').querySelector('div');
                const icon = container.querySelector('.flex-shrink-0');
                const text = container.querySelector('span');
                const checkmark = container.querySelector('.absolute.top-3.right-3');
                const overlay = container.querySelector('.absolute.inset-0');
                
                function updateStyles() {
                    if (checkbox.checked) {
                        // Selected state
                        container.classList.add('border-green-500', 'bg-green-50', 'shadow-lg', 'scale-105');
                        container.classList.remove('border-gray-200');
                        icon.classList.add('bg-green-100');
                        icon.classList.remove('bg-gray-100');
                        icon.querySelector('svg').classList.add('text-green-600');
                        icon.querySelector('svg').classList.remove('text-gray-600');
                        text.classList.add('text-green-900');
                        text.classList.remove('text-gray-900');
                        checkmark.classList.add('border-green-500', 'bg-green-500');
                        checkmark.classList.remove('border-gray-300');
                        checkmark.querySelector('svg').classList.add('opacity-100');
                        checkmark.querySelector('svg').classList.remove('opacity-0');
                        overlay.classList.add('opacity-100');
                        overlay.classList.remove('opacity-0');
                    } else {
                        // Unselected state
                        container.classList.remove('border-green-500', 'bg-green-50', 'shadow-lg', 'scale-105');
                        container.classList.add('border-gray-200');
                        icon.classList.remove('bg-green-100');
                        icon.classList.add('bg-gray-100');
                        icon.querySelector('svg').classList.remove('text-green-600');
                        icon.querySelector('svg').classList.add('text-gray-600');
                        text.classList.remove('text-green-900');
                        text.classList.add('text-gray-900');
                        checkmark.classList.remove('border-green-500', 'bg-green-500');
                        checkmark.classList.add('border-gray-300');
                        checkmark.querySelector('svg').classList.remove('opacity-100');
                        checkmark.querySelector('svg').classList.add('opacity-0');
                        overlay.classList.remove('opacity-100');
                        overlay.classList.add('opacity-0');
                    }
                }
                
                // Initial state
                updateStyles();
                
                // Handle click
                checkbox.addEventListener('change', updateStyles);
            });
        });

        // Add form animation on submit
        document.getElementById('waitlist-form').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            button.innerHTML = '<div class="flex items-center"><svg class="animate-spin h-6 w-6 mr-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Joining Waitlist...</div>';
            button.disabled = true;
        });
    </script>
@endsection