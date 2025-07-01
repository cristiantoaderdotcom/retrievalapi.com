@extends('layouts.website')

@section('title', 'About Us | ReplyElf')
@section('description', 'Meet the team behind ReplyElf and learn about our mission to revolutionize customer communications with AI chatbot technology.')

@section('main')
    <!-- Hero Section -->
    <div class="relative pt-16 pb-20 sm:pt-24 sm:pb-32 bg-gradient-to-b from-blue-50 to-white">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
                    About <span class="text-blue-600">ReplyElf</span>
                </h1>
                <p class="mt-3 max-w-3xl mx-auto text-base text-gray-500 sm:mt-5 sm:text-lg md:mt-5 md:text-xl">
                    We're on a mission to revolutionize how businesses communicate with their customers through intelligent AI.
                </p>
            </div>
        </div>
    </div>

    <!-- Our Story Section -->
    <div class="bg-white py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                        Our Story
                    </h2>
                    <p class="mt-3 max-w-3xl text-lg text-gray-500">
                        ReplyElf was founded in 2022 by a team of AI enthusiasts and customer experience experts who saw the potential for artificial intelligence to transform customer interactions.
                    </p>
                    <div class="mt-8 space-y-4">
                        <p class="text-gray-500">
                            We noticed that many businesses were struggling to provide timely, personalized support to their customers. Traditional customer service methods were either too slow, too expensive, or simply not scalable.
                        </p>
                        <p class="text-gray-500">
                            That's when we had our eureka moment: what if we could harness the power of artificial intelligence to create chatbots that could understand context, learn from interactions, and provide genuinely helpful responses - all without requiring technical expertise to set up?
                        </p>
                        <p class="text-gray-500">
                            After months of research and development, ReplyElf was born. Today, we're proud to serve thousands of businesses worldwide, helping them engage with their customers in more meaningful ways.
                        </p>
                    </div>
                </div>
                <div class="mt-10 lg:mt-0">
                    <img class="rounded-lg shadow-xl" src="{{ asset('img/about/our-story.jpg') }}" alt="Our team at work">
                </div>
            </div>
        </div>
    </div>

    <!-- Our Values Section -->
    <div class="bg-blue-50 py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Our Values
                </h2>
                <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500">
                    The principles that guide everything we do.
                </p>
            </div>

            <div class="mt-16">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="pt-6">
                        <div class="flow-root bg-white rounded-lg shadow-sm px-6 pb-8">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3 bg-blue-600 rounded-md shadow-lg">
                                        <flux:icon name="light-bulb" class="h-6 w-6 text-white" />
                                    </span>
                                </div>
                                <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Innovation</h3>
                                <p class="mt-5 text-base text-gray-500">
                                    We believe in pushing the boundaries of what's possible with AI to create solutions that solve real problems.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <div class="flow-root bg-white rounded-lg shadow-sm px-6 pb-8">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3 bg-blue-600 rounded-md shadow-lg">
                                        <flux:icon name="shield-check" class="h-6 w-6 text-white" />
                                    </span>
                                </div>
                                <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Trust</h3>
                                <p class="mt-5 text-base text-gray-500">
                                    We're committed to building technology that's reliable, secure, and earns our customers' trust every day.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <div class="flow-root bg-white rounded-lg shadow-sm px-6 pb-8">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3 bg-blue-600 rounded-md shadow-lg">
                                        <flux:icon name="sparkles" class="h-6 w-6 text-white" />
                                    </span>
                                </div>
                                <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Simplicity</h3>
                                <p class="mt-5 text-base text-gray-500">
                                    We make powerful technology accessible through intuitive design and a focus on user experience.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <div class="flow-root bg-white rounded-lg shadow-sm px-6 pb-8">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3 bg-blue-600 rounded-md shadow-lg">
                                        <flux:icon name="users" class="h-6 w-6 text-white" />
                                    </span>
                                </div>
                                <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Empowerment</h3>
                                <p class="mt-5 text-base text-gray-500">
                                    We empower businesses of all sizes to deliver exceptional customer experiences with AI.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <div class="flow-root bg-white rounded-lg shadow-sm px-6 pb-8">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3 bg-blue-600 rounded-md shadow-lg">
                                        <flux:icon name="chart-bar" class="h-6 w-6 text-white" />
                                    </span>
                                </div>
                                <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Impact</h3>
                                <p class="mt-5 text-base text-gray-500">
                                    We measure our success by the positive impact our technology has on our customers' businesses.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <div class="flow-root bg-white rounded-lg shadow-sm px-6 pb-8">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3 bg-blue-600 rounded-md shadow-lg">
                                        <flux:icon name="heart" class="h-6 w-6 text-white" />
                                    </span>
                                </div>
                                <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Customer Focus</h3>
                                <p class="mt-5 text-base text-gray-500">
                                    Our customers are at the heart of everything we do. We listen, learn, and evolve with their needs.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leadership Team Section -->
    <div class="bg-white py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Our Team
                </h2>
                <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500">
                    Meet the people behind ReplyElf.
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 gap-12 sm:grid-cols-2 lg:grid-cols-3">
                <div class="text-center">
                    <div class="space-y-4">
                        <img class="mx-auto h-40 w-40 rounded-full" src="{{ asset('img/team/ceo.jpg') }}" alt="CEO Portrait">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Alex Johnson</h3>
                            <p class="text-indigo-600">CEO & Co-Founder</p>
                        </div>
                        <div class="text-gray-500 text-sm">
                            <p>Former AI researcher at Google with a passion for making technology accessible.</p>
                        </div>
                        <div class="flex justify-center space-x-5">
                            <a href="#" class="text-gray-400 hover:text-gray-500">
                                <flux:icon name="linkedin" class="h-5 w-5" />
                            </a>
                            <a href="#" class="text-gray-400 hover:text-gray-500">
                                <flux:icon name="twitter" class="h-5 w-5" />
                            </a>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <div class="space-y-4">
                        <img class="mx-auto h-40 w-40 rounded-full" src="{{ asset('img/team/cto.jpg') }}" alt="CTO Portrait">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Sophia Lee</h3>
                            <p class="text-indigo-600">CTO & Co-Founder</p>
                        </div>
                        <div class="text-gray-500 text-sm">
                            <p>Machine learning expert with over 10 years of experience building AI systems at scale.</p>
                        </div>
                        <div class="flex justify-center space-x-5">
                            <a href="#" class="text-gray-400 hover:text-gray-500">
                                <flux:icon name="linkedin" class="h-5 w-5" />
                            </a>
                            <a href="#" class="text-gray-400 hover:text-gray-500">
                                <flux:icon name="twitter" class="h-5 w-5" />
                            </a>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <div class="space-y-4">
                        <img class="mx-auto h-40 w-40 rounded-full" src="{{ asset('img/team/cpo.jpg') }}" alt="CPO Portrait">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Marcus Chen</h3>
                            <p class="text-indigo-600">Chief Product Officer</p>
                        </div>
                        <div class="text-gray-500 text-sm">
                            <p>Product leader with a background in UX design and a focus on creating intuitive experiences.</p>
                        </div>
                        <div class="flex justify-center space-x-5">
                            <a href="#" class="text-gray-400 hover:text-gray-500">
                                <flux:icon name="linkedin" class="h-5 w-5" />
                            </a>
                            <a href="#" class="text-gray-400 hover:text-gray-500">
                                <flux:icon name="twitter" class="h-5 w-5" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-blue-700">
        <div class="max-w-2xl mx-auto py-16 px-4 text-center sm:py-20 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                <span class="block">Join us on our mission</span>
            </h2>
            <p class="mt-4 text-lg leading-6 text-blue-200">
                Ready to revolutionize how your business communicates with customers?
            </p>
            <div class="mt-8 flex justify-center gap-4">
                <flux:button variant="primary" href="{{ route('register') }}" class="px-5 py-3 text-base font-medium bg-white text-blue-600 hover:bg-blue-50">
                    Start for free
                </flux:button>
                <flux:button variant="ghost" href="{{ route('careers') }}" class="px-5 py-3 text-base font-medium text-white border border-white">
                    Join our team
                </flux:button>
            </div>
        </div>
    </div>
@endsection 