<footer class="bg-gray-900 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="space-y-4">
                <img src="{{ asset('img/logo-white.svg') }}" alt="{{ config('app.name') }}" class="h-8">
                <p class="text-gray-400 text-sm max-w-xs">
                    Create intelligent chatbots that boost engagement, generate leads, and provide 24/7 customer support.
                </p>
                <div class="flex space-x-4">
                    {{-- <a href="#" class="text-gray-400 hover:text-white">
                        <flux:icon name="facebook" class="h-5 w-5" />
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <flux:icon name="twitter" class="h-5 w-5" />
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <flux:icon name="linkedin" class="h-5 w-5" />
                    </a> --}}
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">Product</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('features.chatbot') }}" class="text-gray-400 hover:text-white text-sm">AI Chatbot</a></li>
                    <li><a href="{{ route('features.analytics') }}" class="text-gray-400 hover:text-white text-sm">Analytics</a></li>
                    <li><a href="{{ route('features.integrations') }}" class="text-gray-400 hover:text-white text-sm">Integrations</a></li>
                    <li><a href="{{ route('pricing') }}" class="text-gray-400 hover:text-white text-sm">Pricing</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">Resources</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('resources.blog') }}" class="text-gray-400 hover:text-white text-sm">Blog</a></li>
                    <li><a href="{{ route('resources.documentation') }}" class="text-gray-400 hover:text-white text-sm">Documentation</a></li>
                    <li><a href="{{ route('resources.case-studies') }}" class="text-gray-400 hover:text-white text-sm">Case Studies</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">Company</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('about') }}" class="text-gray-400 hover:text-white text-sm">About Us</a></li>
                    <li><a href="{{ route('careers') }}" class="text-gray-400 hover:text-white text-sm">Careers</a></li>
                    <li><a href="{{ route('contact') }}" class="text-gray-400 hover:text-white text-sm">Contact</a></li>
                    <li><a href="{{ route('privacy') }}" class="text-gray-400 hover:text-white text-sm">Privacy</a></li>
                    <li><a href="{{ route('terms') }}" class="text-gray-400 hover:text-white text-sm">Terms</a></li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 text-sm">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
            <div class="mt-4 md:mt-0">
                <img src="{{ asset('img/payment-methods.svg') }}" alt="Payment Methods" class="h-8">
            </div>
        </div>
    </div>
</footer> 