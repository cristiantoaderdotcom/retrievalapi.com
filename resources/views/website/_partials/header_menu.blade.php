<nav class="relativ">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="flex items-center justify-between py-3">
            <!-- Logo -->
            <div class="flex shrink-0 items-center">
                <a href="{{ route('home') }}" class="flex items-center">
                    <span class="sr-only">{{ config('app.name') }}</span>
                    <img class="h-10 w-auto" src="{{ asset('assets/images/logo/logo.png') }}" alt="{{ config('app.name') }} Logo">
                </a>
            </div>
            
            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-expanded="false">
                    <span class="sr-only">Open menu</span>
                    <svg id="mobile-menu-icon-open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg id="mobile-menu-icon-close" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Desktop Navigation Items -->
            <div class="hidden md:flex md:flex-1 md:items-center md:justify-between">
                <!-- Center navigation -->
                <div class="mx-auto flex space-x-8">
                    <!-- Product Dropdown -->
                    <div class="relative">
                        <button id="product-dropdown-button" type="button" class="dropdown-button group inline-flex items-center rounded-md  text-md font-medium text-gray-600 hover:text-gray-900 " aria-expanded="false" data-dropdown="product">
                            <span>Product</span>
                            <svg class="dropdown-arrow ml-1 h-4 w-4 text-gray-400 group-hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Solutions Dropdown -->
                    <div class="relative">
                        <button id="solutions-dropdown-button" type="button" class="dropdown-button group inline-flex items-center rounded-md text-md font-medium text-gray-600 hover:text-gray-900 " aria-expanded="false" data-dropdown="solutions">
                            <span>Solutions</span>
                            <svg class="dropdown-arrow ml-1 h-4 w-4 text-gray-400 group-hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    {{-- <!-- Resources Dropdown -->
                    <div class="relative">
                        <button id="resources-dropdown-button" type="button" class="dropdown-button group inline-flex items-center rounded-md  text-md font-medium text-gray-600 hover:text-gray-900 " aria-expanded="false" data-dropdown="resources">
                            <span>Resources</span>
                            <svg class="dropdown-arrow ml-1 h-4 w-4 text-gray-400 group-hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div> --}}

                    <div class="relative">
                        <a href="{{ route('pricing') }}" class="text-md font-medium text-gray-600 hover:text-gray-900 ">Pricing</a>       
                    </div>
                </div>
                
                <!-- Right side navigation buttons -->
                <div class="flex items-center ml-8 space-x-4">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                        Sign in
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-green-700 ">
                        Start now is free!
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Dropdown panels container - centralized for all dropdowns -->
    <div id="dropdown-container" class="absolute left-0 right-0 z-10 mt-2 transform px-2 sm:px-0 opacity-0 pointer-events-none transition-opacity duration-200 ease-in-out">
        <div class="mx-auto max-w-7xl">
            <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-opacity-5">
                <div class="relative border rounded-lg bg-white border-gray-200 p-7">
                    <div id="product-dropdown-content" class="dropdown-content hidden">
                        @include('website._partials.header_dropdowns.product')
                    </div>
                    
                    <div id="solutions-dropdown-content" class="dropdown-content hidden">
                        @include('website._partials.header_dropdowns.solutions')
                    </div>
                    
                    <div id="resources-dropdown-content" class="dropdown-content hidden">
                        @include('website._partials.header_dropdowns.resources')
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden absolute inset-x-0 top-0 z-10 origin-top-right transform p-2 transition md:hidden">
        <div class="divide-y-2 divide-gray-50 rounded-lg bg-white shadow-lg ring-1 ring-opacity-5">
            <div class="px-5 pt-5 pb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <img class="h-8 w-auto" src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }} Logo">
                    </div>
                    <div class="-mr-2">
                        <button id="close-mobile-menu-button" type="button" class="inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                            <span class="sr-only">Close menu</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mt-6">
                    <nav class="grid gap-y-6">
                        <a href="" class="-m-3 flex items-center rounded-md p-3 hover:bg-gray-50">
                            <span class="text-sm font-medium text-gray-900">Product</span>
                        </a>
                        <a href="" class="-m-3 flex items-center rounded-md p-3 hover:bg-gray-50">
                            <span class="text-sm font-medium text-gray-900">Solutions</span>
                        </a>
                        <a href="" class="-m-3 flex items-center rounded-md p-3 hover:bg-gray-50">
                            <span class="text-sm font-medium text-gray-900">Resources</span>
                        </a>
                        <a href="" class="-m-3 flex items-center rounded-md p-3 hover:bg-gray-50">
                            <span class="text-sm font-medium text-gray-900">Pricing</span>
                        </a>
                    </nav>
                </div>
            </div>
            <div class="space-y-6 py-6 px-5">
                <div class="grid gap-y-4">
                    <a href="{{ route('register') }}" class="flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        Sign up
                    </a>
                    <a href="{{ route('login') }}" class="flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        Sign in
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables
            const dropdownButtons = document.querySelectorAll('.dropdown-button');
            const dropdownContainer = document.getElementById('dropdown-container');
            const dropdownContents = document.querySelectorAll('.dropdown-content');
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const closeMobileMenuButton = document.getElementById('close-mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuIconOpen = document.getElementById('mobile-menu-icon-open');
            const mobileMenuIconClose = document.getElementById('mobile-menu-icon-close');
            
            let currentDropdown = null;
            
            // Functions
            function showDropdown(dropdownId) {
                // First hide all dropdowns
                dropdownContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Reset all arrows
                document.querySelectorAll('.dropdown-arrow').forEach(arrow => {
                    arrow.classList.remove('rotate-180');
                });
                
                // Show the selected dropdown
                const targetContent = document.getElementById(`${dropdownId}-dropdown-content`);
                if (targetContent) {
                    targetContent.classList.remove('hidden');
                    
                    // Rotate the arrow
                    const button = document.getElementById(`${dropdownId}-dropdown-button`);
                    if (button) {
                        const arrow = button.querySelector('.dropdown-arrow');
                        if (arrow) {
                            arrow.classList.add('rotate-180');
                        }
                    }
                }
                
                // Show container with animation
                dropdownContainer.classList.remove('opacity-0', 'pointer-events-none');
                dropdownContainer.classList.add('opacity-100');
                
                currentDropdown = dropdownId;
            }
            
            function hideAllDropdowns() {
                // Hide all dropdown contents
                dropdownContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Reset all arrows
                document.querySelectorAll('.dropdown-arrow').forEach(arrow => {
                    arrow.classList.remove('rotate-180');
                });
                
                // Hide container with animation
                dropdownContainer.classList.remove('opacity-100');
                dropdownContainer.classList.add('opacity-0', 'pointer-events-none');
                
                currentDropdown = null;
            }
            
            function toggleDropdown(dropdownId) {
                if (currentDropdown === dropdownId) {
                    hideAllDropdowns();
                } else {
                    showDropdown(dropdownId);
                }
            }
            
            function toggleMobileMenu() {
                if (mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.remove('hidden');
                    mobileMenuIconOpen.classList.add('hidden');
                    mobileMenuIconClose.classList.remove('hidden');
                } else {
                    mobileMenu.classList.add('hidden');
                    mobileMenuIconOpen.classList.remove('hidden');
                    mobileMenuIconClose.classList.add('hidden');
                }
            }
            
            // Event Listeners
            dropdownButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const dropdownId = this.getAttribute('data-dropdown');
                    toggleDropdown(dropdownId);
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-button') && !e.target.closest('#dropdown-container')) {
                    hideAllDropdowns();
                }
            });
            
            // Mobile menu
            mobileMenuButton.addEventListener('click', toggleMobileMenu);
            closeMobileMenuButton.addEventListener('click', toggleMobileMenu);
            
            // Close on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    hideAllDropdowns();
                    if (!mobileMenu.classList.contains('hidden')) {
                        toggleMobileMenu();
                    }
                }
            });
        });
    </script>
</nav> 