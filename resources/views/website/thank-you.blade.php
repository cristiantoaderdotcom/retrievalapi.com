@extends('layouts.website')

@section('main')
<div class="bg-white py-16 md:py-24">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="bg-white border border-zinc-200 rounded-2xl shadow-xl p-8 md:p-12">
            <div class="flex justify-center mb-8">
                <div class="bg-green-100 rounded-full p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-green-600">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            
            <div class="text-center mb-10">
                <h1 class="text-3xl md:text-4xl font-bold text-zinc-900 mb-4">Thank You for Your Purchase!</h1>
                <p class="text-lg text-zinc-600 mb-4">Your order has been successfully processed.</p>
                <div class="bg-orange-50 border border-orange-100 rounded-xl p-6 max-w-xl mx-auto my-8">
                    <h2 class="text-xl font-semibold text-zinc-900 mb-3">What happens next?</h2>
                    <ul class="text-left space-y-4">
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-orange-500 mt-0.5 mr-3 flex-shrink-0">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="font-medium">Check your email:</span> Please check your email from <span class="font-medium">noreply@iframeai.com</span> with login details.
                            </div>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-orange-500 mt-0.5 mr-3 flex-shrink-0">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="font-medium">Login to your account:</span> Use the email and password you created during checkout to access the IframeAI platform.
                            </div>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-orange-500 mt-0.5 mr-3 flex-shrink-0">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="font-medium">Start building:</span> Create your first AI chatbot and integrate it with your website in minutes.
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="{{ route('login', request()->uuid ?? '') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-orange-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                    Login to Your Account
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 ml-2">
                        <path fill-rule="evenodd" d="M12.97 3.97a.75.75 0 011.06 0l7.5 7.5a.75.75 0 010 1.06l-7.5 7.5a.75.75 0 11-1.06-1.06l6.22-6.22H3a.75.75 0 010-1.5h16.19l-6.22-6.22a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="{{  route('login', request()->uuid ?? '') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-zinc-100 px-8 py-4 text-base font-semibold text-zinc-700 transition hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-zinc-300 focus:ring-offset-2">
                    Go to Dashboard
                </a>
            </div>
        </div>
        
        <div class="mt-12 bg-zinc-50 border border-zinc-200 rounded-xl p-6 text-center">
            <h3 class="font-semibold text-zinc-900 mb-2">Need Help?</h3>
            <p class="text-zinc-600 mb-4">If you have any questions or didn't receive your email, our support team is here to help.</p>
            <a href="{{ route('home') }}" class="inline-flex items-center font-medium text-orange-600 hover:text-orange-700">
                Back to Homepage
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 ml-1">
                    <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection 