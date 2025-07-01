@extends('layouts.website')

@section('main')
<div class="bg-white py-16 md:py-24">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Error Card -->
        <div class="bg-white border border-red-200 rounded-2xl shadow-xl p-8 md:p-12 mb-16">
            <!-- Error Icon -->
            <div class="flex justify-center mb-8">
                <div class="bg-red-100 rounded-full p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-red-600">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="text-center mb-6">
                <h1 class="text-3xl md:text-4xl font-bold text-zinc-900 mb-4">Payment Processing Error</h1>
                <p class="text-lg text-zinc-600 mb-6">We encountered an issue while processing your payment. Your card has not been charged.</p>
                
                <div class="bg-red-50 border border-red-100 rounded-xl p-6 max-w-xl mx-auto mb-8">
                    <h2 class="text-xl font-semibold text-zinc-900 mb-3">Common Reasons for Payment Failure:</h2>
                    <ul class="text-left space-y-3">
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0">
                                <path fill-rule="evenodd" d="M11.484 2.17a.75.75 0 011.032 0 11.209 11.209 0 007.877 3.08.75.75 0 01.722.515 12.74 12.74 0 01.635 3.985c0 5.942-4.064 10.933-9.563 12.348a.76.76 0 01-.374 0C6.314 20.683 2.25 15.692 2.25 9.75c0-1.39.223-2.73.635-3.985a.75.75 0 01.722-.516l.143.001c2.996 0 5.718-1.17 7.734-3.08zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zM12 15a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75v-.008a.75.75 0 00-.75-.75H12z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="font-medium">Insufficient funds</span> in your account
                            </div>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0">
                                <path fill-rule="evenodd" d="M11.484 2.17a.75.75 0 011.032 0 11.209 11.209 0 007.877 3.08.75.75 0 01.722.515 12.74 12.74 0 01.635 3.985c0 5.942-4.064 10.933-9.563 12.348a.76.76 0 01-.374 0C6.314 20.683 2.25 15.692 2.25 9.75c0-1.39.223-2.73.635-3.985a.75.75 0 01.722-.516l.143.001c2.996 0 5.718-1.17 7.734-3.08zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zM12 15a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75v-.008a.75.75 0 00-.75-.75H12z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="font-medium">Temporary authorization issue</span> with your card
                            </div>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0">
                                <path fill-rule="evenodd" d="M11.484 2.17a.75.75 0 011.032 0 11.209 11.209 0 007.877 3.08.75.75 0 01.722.515 12.74 12.74 0 01.635 3.985c0 5.942-4.064 10.933-9.563 12.348a.76.76 0 01-.374 0C6.314 20.683 2.25 15.692 2.25 9.75c0-1.39.223-2.73.635-3.985a.75.75 0 01.722-.516l.143.001c2.996 0 5.718-1.17 7.734-3.08zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zM12 15a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75v-.008a.75.75 0 00-.75-.75H12z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="font-medium">Incorrect card information</span> entered during checkout
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Action Message -->
            <div class="text-center">
                <p class="text-lg text-zinc-700 font-medium mb-6">Please try again with a different payment method or contact your bank if the issue persists.</p>
            </div>
        </div>
    </div>
</div>

<!-- Pricing Section -->
<div class="bg-orange-50 py-16">
    <div class="container mx-auto px-4 max-w-4xl text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 mb-3">Try Again <span class="text-orange-600">With Another Payment Method</span></h2>
        <p class="text-lg text-zinc-600">You're just one step away from getting started with IframeAI</p>
    </div>
    
    <!-- Include Pricing Section -->
    @includeIf('website._partials.index.pricing')
</div>

@endsection 