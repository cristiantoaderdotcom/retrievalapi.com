{{-- <div x-data="{ showCookieConsent: true }" x-init="() => { 
    setTimeout(() => {
        if (!localStorage.getItem('cookie-consent')) {
            showCookieConsent = true;
        } else {
            showCookieConsent = false;
        }
    }, 1000);
}" x-show="showCookieConsent" class="fixed bottom-0 inset-x-0 z-50 px-4 py-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-6 md:flex md:items-center md:justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <flux:icon name="shield-check" class="h-8 w-8 text-blue-500" />
                    </div>
                    <div class="ml-3 md:flex-1">
                        <p class="text-sm text-gray-700">
                            We use cookies to enhance your browsing experience, serve personalized ads or content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.
                        </p>
                        <div class="mt-2">
                            <a href="{{ route('privacy') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                Cookie Policy
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="mt-4 md:mt-0 md:ml-6 flex flex-shrink-0 flex-col sm:flex-row gap-2">
                    <flux:button variant="ghost" @click="showCookieConsent = false; localStorage.setItem('cookie-consent', 'necessary')">
                        Necessary Only
                    </flux:button>
                    <flux:button variant="primary" @click="showCookieConsent = false; localStorage.setItem('cookie-consent', 'all')">
                        Accept All
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>  --}}