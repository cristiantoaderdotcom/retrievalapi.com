@extends('layouts.boilerplate')
@section('body')
	<div class="mx-auto flex min-h-screen flex-col justify-center xl:flex-row">
		<div class="relative flex flex-1 items-center justify-center px-2 lg:px-0">
			<div class="relative space-y-6 py-8 z-20 mx-auto w-full max-w-md">
				<a class="block" href="/">
					<img loading="lazy" class="h-16 mx-auto" src="{{ asset('/assets/images/logo/logo.png') }}" width="auto" height="56" alt="" />
				</a>
				<div class="rounded-3xl bg-white p-6 sm:p-10 shadow-lg border border-zinc-200">
					@yield('main')
				</div>
			</div>
		</div>

		<div class="relative flex-1 p-4 hidden lg:block z-10">
			<div class="space-y-8 text-zinc-900 relative rounded-2xl h-full w-full bg-white/95 backdrop-blur-lg flex flex-col items-start justify-center p-16 border border-green-200">
				<div>
					<h2 class="text-2xl font-semibold text-zinc-900 flex items-center gap-2">
						<flux:icon name="sparkles" class="size-6 text-green-500" />
						Welcome to ReplyElf
					</h2>
					<p class="text-zinc-600 mt-2">Your all-in-one AI chatbot platform for better customer engagement and lead generation</p>
				</div>

				<div class="space-y-6">
					<div class="flex items-start gap-3">
						<div class="mt-1 p-2 bg-green-100 rounded-lg">
							<flux:icon name="chat-bubble-left-right" class="size-5 text-green-600" />
						</div>
						<div>
							<h3 class="font-medium text-zinc-900">Unlimited AI Chatbots</h3>
							<p class="text-sm text-zinc-600">Create and manage multiple chatbots for different purposes with up to 500,000 characters of training each</p>
						</div>
					</div>

					<div class="flex items-start gap-3">
						<div class="mt-1 p-2 bg-green-100 rounded-lg">
							<flux:icon name="clock" class="size-5 text-green-600" />
						</div>
						<div>
							<h3 class="font-medium text-zinc-900">24/7 Customer Support</h3>
							<p class="text-sm text-zinc-600">Provide instant responses to your website visitors around the clock without human intervention</p>
						</div>
					</div>

					<div class="flex items-start gap-3">
						<div class="mt-1 p-2 bg-green-100 rounded-lg">
							<flux:icon name="user-group" class="size-5 text-green-600" />
						</div>
						<div>
							<h3 class="font-medium text-zinc-900">Lead Generation</h3>
							<p class="text-sm text-zinc-600">Capture and manage unlimited leads through intelligent chatbot conversations</p>
						</div>
					</div>

					<div class="flex items-start gap-3">
						<div class="mt-1 p-2 bg-green-100 rounded-lg">
							<flux:icon name="chart-bar" class="size-5 text-green-600" />
						</div>
						<div>
							<h3 class="font-medium text-zinc-900">Advanced Analytics</h3>
							<p class="text-sm text-zinc-600">Track performance, analyze conversations, and measure ROI with detailed insights</p>
						</div>
					</div>
				</div>

				<div class="pt-6 border-t border-green-100 w-full">
					<p class="text-sm text-zinc-500">Need help? Contact our support team at support@replyelf.com</p>
				</div>
			</div>
		</div>
	</div>

	<div class="fixed top-0 bottom-0 z-0 h-full w-full overflow-hidden bg-gradient-to-br from-green-500/50 to-green-500/30">
		<div class="animate-gradient-3 w-[1242px] h-[818px] translate-x-[-102%] translate-y-[-52%] rotate-[-9deg] top-[50%] left-[50%] origin-center absolute rounded-full bg-linear-to-b from-green-200/50 to-green-100/30 mix-blend-normal blur-[228px] delay-600"></div>
		<div class="absolute top-[-50px] left-[-50px] bg-repeat bg-[length:703px] w-[calc(100%_+_50px)] h-[calc(100%_+_50px)] opacity-30" style="background-image: url({{ asset('assets/images/dashboard/grid-01.svg') }});"></div>
	</div>
@endsection
