@extends('layouts.website')

@section('title', 'Pricing | ReplyElf')
@section('description', 'Simple, transparent pricing for businesses of all sizes. Choose the plan that works best for your needs.')

@section('main')
    <div class="py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
                    Simple, transparent pricing
                </h1>
                <p class="mt-5 max-w-xl mx-auto text-xl text-gray-500">
                    No hidden fees. No surprises. Just the tools you need to grow your business.
                </p>
            </div>
            
            <div class="mt-12 max-w-lg mx-auto md:max-w-none">
                <div x-data="{ annual: true }" class="flex justify-center mb-10">
                    <div class="relative bg-gray-100 rounded-full p-1 flex">
                        <button 
                            @click="annual = false" 
                            :class="{'bg-white shadow-sm': !annual, 'text-gray-500': annual}"
                            class="relative rounded-full py-2 px-6 text-sm font-medium whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            Monthly
                        </button>
                        <button 
                            @click="annual = true" 
                            :class="{'bg-white shadow-sm': annual, 'text-gray-500': !annual}"
                            class="relative ml-0.5 rounded-full py-2 px-6 text-sm font-medium whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            Annual <span class="text-blue-600 font-semibold">Save 20%</span>
                        </button>
                    </div>
                </div>
                
                <div class="grid gap-8 lg:grid-cols-3">
                    <!-- Starter Plan -->
                    <div class="border border-gray-200 rounded-lg shadow-sm bg-white overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900">Starter</h2>
                            <p class="mt-1 text-sm text-gray-500">Perfect for small businesses getting started</p>
                            <div x-cloak class="mt-4 flex items-baseline">
                                <div x-show="annual" class="flex flex-col">
                                    <span class="text-4xl font-extrabold tracking-tight text-gray-900">$39</span>
                                    <span class="text-sm font-medium text-gray-500 mt-1">per month, billed annually</span>
                                </div>
                                <div x-show="!annual" class="flex flex-col">
                                    <span class="text-4xl font-extrabold tracking-tight text-gray-900">$49</span>
                                    <span class="text-sm font-medium text-gray-500 mt-1">per month</span>
                                </div>
                            </div>
                            <div class="mt-6">
                                <flux:button variant="primary" href="{{ route('register') }}" class="w-full">
                                    Start free trial
                                </flux:button>
                            </div>
                            <div class="mt-2 text-center">
                                <p class="text-xs text-gray-500">No credit card required</p>
                            </div>
                        </div>
                        <div class="px-6 pt-2 pb-6">
                            <h3 class="text-sm font-medium text-gray-900 tracking-wide uppercase">What's included</h3>
                            <ul class="mt-4 space-y-3">
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">1 AI chatbot</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">5,000 messages/month</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Website integration</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Basic analytics</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Email support</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Professional Plan -->
                    <div class="border-2 border-blue-600 rounded-lg shadow-md bg-white overflow-hidden relative">
                        <div class="absolute top-0 inset-x-0">
                            <div class="bg-blue-600 text-white text-xs text-center font-medium px-3 py-1">
                                MOST POPULAR
                            </div>
                        </div>
                        <div class="p-6 pt-8">
                            <h2 class="text-lg font-medium text-gray-900">Professional</h2>
                            <p class="mt-1 text-sm text-gray-500">Ideal for growing businesses</p>
                            <div x-cloak class="mt-4 flex items-baseline">
                                <div x-show="annual" class="flex flex-col">
                                    <span class="text-4xl font-extrabold tracking-tight text-gray-900">$99</span>
                                    <span class="text-sm font-medium text-gray-500 mt-1">per month, billed annually</span>
                                </div>
                                <div x-show="!annual" class="flex flex-col">
                                    <span class="text-4xl font-extrabold tracking-tight text-gray-900">$119</span>
                                    <span class="text-sm font-medium text-gray-500 mt-1">per month</span>
                                </div>
                            </div>
                            <div class="mt-6">
                                <flux:button variant="primary" href="{{ route('register') }}" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600">
                                    Start free trial
                                </flux:button>
                            </div>
                            <div class="mt-2 text-center">
                                <p class="text-xs text-gray-500">No credit card required</p>
                            </div>
                        </div>
                        <div class="px-6 pt-2 pb-6">
                            <h3 class="text-sm font-medium text-gray-900 tracking-wide uppercase">What's included</h3>
                            <ul class="mt-4 space-y-3">
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">5 AI chatbots</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">25,000 messages/month</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Website & WhatsApp integration</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Advanced analytics</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Priority support</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Custom branding</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">API access</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Enterprise Plan -->
                    <div class="border border-gray-200 rounded-lg shadow-sm bg-white overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900">Enterprise</h2>
                            <p class="mt-1 text-sm text-gray-500">For large organizations with custom needs</p>
                            <div class="mt-4 flex items-baseline">
                                <div class="flex flex-col">
                                    <span class="text-4xl font-extrabold tracking-tight text-gray-900">Custom</span>
                                    <span class="text-sm font-medium text-gray-500 mt-1">Contact sales for pricing</span>
                                </div>
                            </div>
                            <div class="mt-6">
                                <flux:button variant="ghost" href="{{ route('contact') }}" class="w-full border-2 border-gray-300">
                                    Contact sales
                                </flux:button>
                            </div>
                            <div class="mt-2 text-center">
                                <p class="text-xs text-gray-500">Custom solutions available</p>
                            </div>
                        </div>
                        <div class="px-6 pt-2 pb-6">
                            <h3 class="text-sm font-medium text-gray-900 tracking-wide uppercase">What's included</h3>
                            <ul class="mt-4 space-y-3">
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Unlimited AI chatbots</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Unlimited messages</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">All integrations</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Enterprise analytics</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Dedicated account manager</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">SSO & advanced security</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">SLA & uptime guarantee</span>
                                </li>
                                <li class="flex items-start">
                                    <flux:icon.check class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-3 text-sm text-gray-700">Custom model training</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- FAQ Section -->
            <div class="mt-20">
                <div class="max-w-3xl mx-auto">
                    <h2 class="text-3xl font-extrabold text-gray-900 text-center">
                        Frequently asked questions
                    </h2>
                    <div class="mt-12">
                        <flux:accordion>
                            <flux:accordion.item>
                                <flux:accordion.heading>
                                    Can I switch plans later?
                                </flux:accordion.heading>
                                <flux:accordion.content>
                                    <p class="text-gray-700">
                                        Yes, you can upgrade or downgrade your plan at any time. When upgrading, you'll get immediate access to the new features. When downgrading, the change will take effect at the end of your current billing cycle.
                                    </p>
                                </flux:accordion.content>
                            </flux:accordion.item>
                            
                            <flux:accordion.item>
                                <flux:accordion.heading>
                                    What happens if I exceed my monthly message limit?
                                </flux:accordion.heading>
                                <flux:accordion.content>
                                    <p class="text-gray-700">
                                        If you exceed your monthly message limit, you'll still be able to use your chatbots. We'll notify you when you're approaching your limit, and you can choose to upgrade your plan or purchase additional messages as needed.
                                    </p>
                                </flux:accordion.content>
                            </flux:accordion.item>
                            
                            <flux:accordion.item>
                                <flux:accordion.heading>
                                    Do you offer a free trial?
                                </flux:accordion.heading>
                                <flux:accordion.content>
                                    <p class="text-gray-700">
                                        Yes, we offer a 14-day free trial on all our plans. No credit card is required to start your trial. You'll get full access to all features included in your selected plan.
                                    </p>
                                </flux:accordion.content>
                            </flux:accordion.item>
                            
                            <flux:accordion.item>
                                <flux:accordion.heading>
                                    Can I get a refund if I'm not satisfied?
                                </flux:accordion.heading>
                                <flux:accordion.content>
                                    <p class="text-gray-700">
                                        We offer a 30-day money-back guarantee for annual subscriptions. If you're not satisfied with our service for any reason, simply contact our support team within 30 days of purchase for a full refund.
                                    </p>
                                </flux:accordion.content>
                            </flux:accordion.item>
                            
                            <flux:accordion.item>
                                <flux:accordion.heading>
                                    Do you offer discounts for nonprofits or educational institutions?
                                </flux:accordion.heading>
                                <flux:accordion.content>
                                    <p class="text-gray-700">
                                        Yes, we offer special pricing for nonprofit organizations, educational institutions, and startups. Please contact our sales team to learn more about our discount programs.
                                    </p>
                                </flux:accordion.content>
                            </flux:accordion.item>
                        </flux:accordion>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 