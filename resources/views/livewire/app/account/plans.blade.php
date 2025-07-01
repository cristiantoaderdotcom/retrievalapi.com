<flux:card class="container mx-auto space-y-6">
	@includeIf('livewire.app.account._partials.navbar')

	<div class="space-y-8">
		<div class="mx-auto max-w-3xl space-y-4 text-center">
			<flux:heading class="text-xl font-bold text-gray-900">
				Upgrade to <span class="text-amber-500">Pro</span> and Unlock IframeAI Full Potential
			</flux:heading>
			<flux:subheading class="text-md mx-auto max-w-2xl text-gray-600">
				Get access to advanced features, higher AI token limits, and premium support to create more powerful AI conversational experiences.
			</flux:subheading>
		</div>

		<div class="mx-auto grid max-w-5xl gap-8 md:grid-cols-2">
			<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
				<div class="p-8">
					<div class="flex items-start justify-between">
						<div>
							<h3 class="text-xl font-bold text-gray-900">Standard Plan</h3>
							<p class="mt-1 text-sm text-gray-500">Get started with basic features</p>
						</div>
						<span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800">
							Current Plan
						</span>
					</div>
				</div>

				<div class="px-8 pb-8">
					<h4 class="mb-4 text-sm font-medium text-gray-900">What's included:</h4>
					<ul class="space-y-3">
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Unlimited Projects (Chatbots)</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Unlimited Leads Collected</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">20M AI Tokens Usage Per Account</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Up to 20k Embeds Loads per Month</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Up to 2M Characters of Context Per Account</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Up to 500k Characters of Context per Embed</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Multiple Training Sources (Websites, Files, Text)</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Access to Lead Collector System</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Chat Interface Customization</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">AI Preferences Customization</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Detailed Conversation Analytics</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Retrain AI from Past Conversations</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Embed Code for Any Website Platform</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">24/7 Chatbot Operations</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Built-in Leads Management System</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Multilingual Support</span>
						</li>
					</ul>
				</div>
			</div>

			<div class="relative rounded-2xl border-2 border-amber-500 bg-white shadow-lg">
				<div class="absolute right-0 top-0">
					<div class="translate-x-4 translate-y-1 rotate-12 bg-amber-500 px-4 py-1 text-xs font-bold uppercase text-white">
						Recommended
					</div>
				</div>

				<div class="p-8">
					<div class="flex items-start justify-between">
						<div>
							<h3 class="text-xl font-bold text-amber-600">Pro Plan</h3>
							<p class="mt-1 text-sm text-gray-500">For professionals and businesses</p>
						</div>
						<span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800">
							Best Value
						</span>
					</div>

					<div class="mt-6">
						<p class="text-sm text-gray-500">One-time Payment</p>
						<div class="mt-1 flex items-baseline">
							<span class="text-4xl font-extrabold text-gray-900">$97</span>
						</div>
					</div>

					<div class="mt-8">
						<form wire:submit="store">
							<flux:button type="submit" class="w-full" variant="primary">
								Upgrade to Pro
							</flux:button>
						</form>
					</div>
				</div>

				<div class="px-8 pb-8">
					<h4 class="mb-4 text-sm font-medium text-gray-900">Everything in Standard, plus:</h4>
					<ul class="space-y-3">
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600"><strong class="text-amber-600">Unlimited</strong> Embeds Loads</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Unlock <strong class="text-amber-600">AI Agent</strong> (In Average up to 35-40% Lower AI Tokens Usage)</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Unlock <strong class="text-amber-600">20+ Appearance Themes</strong></span>
						</li>

						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Unlock <strong class="text-amber-600">Product Catalog</strong> as Training Source (Coming Soon)</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Unlock <strong class="text-amber-600">AI Model Selection</strong> (Coming Soon)</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Priority Support</span>
						</li>
						<li class="flex gap-3">
							<flux:icon.check-circle class="flex-shrink-0 text-green-500" />
							<span class="text-gray-600">Upcoming Features (Coming Soon)</span>
						</li>

					</ul>
				</div>
			</div>
		</div>

		<div class="mx-auto mt-16 max-w-5xl">
			<flux:heading class="mb-12 text-center text-xl">
				Advanced Features Included with <span class="text-amber-500">Pro</span>
			</flux:heading>

			<div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
				<div class="group rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-all duration-300 hover:border-amber-200 hover:shadow-md">
					<div
						class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-amber-50 to-amber-100 p-4 transition-transform duration-300 group-hover:scale-110">
						<flux:icon.globe-alt class="h-7 w-7 text-amber-600" />
					</div>
					<h3 class="mb-3 text-xl font-semibold text-gray-900">Unlimited Embeds</h3>
					<p class="leading-relaxed text-gray-600">Deploy your AI assistants on as many websites and pages as you need without any loading limits.</p>
				</div>

				<!-- Feature 2: AI Agent Training -->
				<div class="group rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-all duration-300 hover:border-amber-200 hover:shadow-md">
					<div
						class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-amber-50 to-amber-100 p-4 transition-transform duration-300 group-hover:scale-110">
						<flux:icon.academic-cap class="h-7 w-7 text-amber-600" />
					</div>
					<h3 class="mb-3 text-xl font-semibold text-gray-900">AI Agent Training</h3>
					<p class="leading-relaxed text-gray-600">Train specialized AI agents for more accurate responses while significantly reducing token usage per message.</p>
				</div>

				<!-- Feature 3: Product Catalog Integration -->
				<div class="group rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-all duration-300 hover:border-amber-200 hover:shadow-md">
					<div
						class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-amber-50 to-amber-100 p-4 transition-transform duration-300 group-hover:scale-110">
						<flux:icon.shopping-bag class="h-7 w-7 text-amber-600" />
					</div>
					<h3 class="mb-3 text-xl font-semibold text-gray-900">Product Catalog</h3>
					<p class="leading-relaxed text-gray-600">Use your product catalog as a training source, allowing AI to answer detailed questions about your offerings.
						<span class="text-sm font-medium text-amber-600">Coming Soon</span>
					</p>
				</div>

				<!-- Feature 4: AI Model Selection -->
				<div class="group rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-all duration-300 hover:border-amber-200 hover:shadow-md">
					<div
						class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-amber-50 to-amber-100 p-4 transition-transform duration-300 group-hover:scale-110">
						<flux:icon.adjustments-horizontal class="h-7 w-7 text-amber-600" />
					</div>
					<h3 class="mb-3 text-xl font-semibold text-gray-900">AI Model Selection</h3>
					<p class="leading-relaxed text-gray-600">Choose from different AI models to optimize for cost, speed, or advanced capabilities. <span
							class="text-sm font-medium text-amber-600">Coming Soon</span></p>
				</div>

				<!-- Feature 5: Priority Support -->
				<div class="group rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-all duration-300 hover:border-amber-200 hover:shadow-md">
					<div
						class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-amber-50 to-amber-100 p-4 transition-transform duration-300 group-hover:scale-110">
						<flux:icon.lifebuoy class="h-7 w-7 text-amber-600" />
					</div>
					<h3 class="mb-3 text-xl font-semibold text-gray-900">Priority Support</h3>
					<p class="leading-relaxed text-gray-600">Get faster responses and personalized assistance from our dedicated support team whenever you need help.</p>
				</div>

				<!-- Feature 6: Future Access -->
				<div class="group rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-all duration-300 hover:border-amber-200 hover:shadow-md">
					<div
						class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-amber-50 to-amber-100 p-4 transition-transform duration-300 group-hover:scale-110">
						<flux:icon.rocket-launch class="h-7 w-7 text-amber-600" />
					</div>
					<h3 class="mb-3 text-xl font-semibold text-gray-900">Early Access</h3>
					<p class="leading-relaxed text-gray-600">Be the first to access new features and improvements as they're released, giving you a competitive edge.</p>
				</div>
			</div>

			<!-- Feature comparison banner -->
			<div class="mt-16 rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-amber-100 p-8">
				<div class="flex flex-col items-center justify-between gap-6 md:flex-row">
					<div class="flex-1">
						<h3 class="mb-2 text-xl font-bold text-gray-900">Ready to take your AI chatbots to the next level?</h3>
						<p class="text-gray-600">Upgrade to Pro today and unlock all the advanced features, higher token limits, and premium support you need to create
							extraordinary conversational experiences.</p>
					</div>
					<flux:button class="flex items-center gap-2" variant="primary">
						<flux:icon.rocket-launch class="h-5 w-5" />
						Upgrade to Pro
					</flux:button>
				</div>
			</div>
		</div>

	</div>
</flux:card>
