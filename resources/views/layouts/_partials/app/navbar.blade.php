<nav :class="{ 'z-50': !$store.screen.lg && collapsed }"
	class="fixed left-0 z-20 flex h-full w-96 flex-col pb-3 pl-3 pr-3 bg-white shadow-2xl transition-all duration-200 lg:relative lg:h-auto lg:w-full lg:max-w-[18rem] lg:bg-transparent lg:pr-0 lg:shadow-none"
	x-show="($store.screen.lg && !collapsed) || (!$store.screen.lg && collapsed)">

	<div class="sticky top-0">
		<div class="mb-10 mt-6 flex h-12 items-center gap-6 px-2">
			<button type="button" x-on:click="collapsed = !collapsed; localStorage.setItem('navbar', collapsed);">
				<x-icons.menu class="size-6" />
			</button>

			<a class="block" href="{{ route('app.index') }}">
				<img alt="IframeAI Logo" class="h-12" height="48" loading="lazy" src="{{ asset('assets/images/logo/logo.png') }}" width="auto" />
			</a>
		</div>

		<div class="space-y-2">
			
			<flux:card class=" shadow-lg">
				<flux:heading class="text-md py-2">
					Choose Workspace
				</flux:heading>
				<flux:separator variant="subtle" class="" />
					
				@if (count($_workspaces) > 0)
					<flux:dropdown align="start" class="flex w-full flex-col truncate" gap="5" position="bottom">
						@if (session('workspace'))
							<flux:navlist.item class="h-12!" icon-trailing="chevron-up-down">
								<div class="flex items-center gap-3 ">
									@if (!empty(session('workspace.icon')))
										<img alt="" class="size-6 rounded-lg" src="{{ session('workspace.icon') }}" />
									@else
										<span class="rounded-xs flex size-6 shrink-0 items-center justify-center bg-zinc-400 text-xs text-black">
											{{ acronym(session('workspace.name')) }}
										</span>
									@endif

									{{ session('workspace.name') }}
								</div>
							</flux:navlist.item>
							<div class="">

								<flux:navlist.group 
									expandable 
									heading="Knowledge Base"
									:expanded="request()->routeIs('app.workspace.knowledge-base.*', session('workspace.uuid'))">

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.knowledge-base.knowledge-base', session('workspace.uuid'))"
										href="{{ route('app.workspace.knowledge-base.knowledge-base', session('workspace.uuid')) }}" 
										icon="document-text">AI Knowledge Base</flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.knowledge-base.import-content', session('workspace.uuid'))"
										href="{{ route('app.workspace.knowledge-base.import-content', session('workspace.uuid')) }}" 
										icon="document-arrow-up">Import Content</flux:navlist.item>
									
									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.knowledge-base.product-*', session('workspace.uuid'))"
										href="{{ route('app.workspace.knowledge-base.product-catalog', session('workspace.uuid')) }}" 
										icon="cube">Product Catalog</flux:navlist.item>
									
									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.knowledge-base.playground', session('workspace.uuid'))"
										href="{{ route('app.workspace.knowledge-base.playground', session('workspace.uuid')) }}" 
										icon="play-circle">Playground</flux:navlist.item>
								
								</flux:navlist.group>

								<flux:navlist.group 
									expandable 
									heading="Engagement"
									:expanded="request()->routeIs('app.workspace.engagement.*', session('workspace.uuid'))">

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.engagement.conversations', session('workspace.uuid'))"
										href="{{ route('app.workspace.engagement.conversations', session('workspace.uuid')) }}" 
										icon="chat-bubble-left-right">Conversations</flux:navlist.item>
									{{-- <flux:navlist.item 
										:current="request()->routeIs('app.workspace.engagement.captured-leads', session('workspace.uuid'))"
										href="{{ route('app.workspace.engagement.captured-leads', session('workspace.uuid')) }}" 
										icon="user-plus">Captured Leads</flux:navlist.item> --}}
									<flux:navlist.item href="#" icon="chart-bar">Analytics <flux:badge size="sm" color="green">Soon</flux:badge></flux:navlist.item>
								</flux:navlist.group>

								<flux:navlist.group 
									expandable 
									heading="Settings"
									:expanded="request()->routeIs('app.workspace.settings.*', session('workspace.uuid'))">

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.settings.ai-preferences', session('workspace.uuid'))"
										href="{{ route('app.workspace.settings.ai-preferences', session('workspace.uuid')) }}" 
										icon="sparkles">AI Preferences</flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.settings.workspace', session('workspace.uuid'))"
										href="{{ route('app.workspace.settings.workspace', session('workspace.uuid')) }}" 
										icon="cog">Workspace</flux:navlist.item>
							
								</flux:navlist.group>

								<flux:navlist.group 
									expandable 
									heading="Connected Platforms"
									:expanded="request()->routeIs('app.workspace.platforms.*', session('workspace.uuid'))">

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.platforms.website', session('workspace.uuid'))"
										href="{{ route('app.workspace.platforms.website', session('workspace.uuid')) }}" 
										icon="globe-alt">Your Website</flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.platforms.api', session('workspace.uuid'))"
										href="{{ route('app.workspace.platforms.api', session('workspace.uuid')) }}" 
										icon="phone">API Integration</flux:navlist.item>
										
									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.platforms.email-inbox', session('workspace.uuid'))"
										href="{{ route('app.workspace.platforms.email-inbox', session('workspace.uuid')) }}" 
										icon="envelope">Email Inbox</flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.platforms.facebook', session('workspace.uuid'))"
										href="{{ route('app.workspace.platforms.facebook', session('workspace.uuid')) }}" 
										icon="chat-bubble-oval-left-ellipsis">Facebook <flux:badge size="sm" color="red">Beta</flux:badge></flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.platforms.instagram', session('workspace.uuid'))"
										href="{{ route('app.workspace.platforms.instagram', session('workspace.uuid')) }}" 
										icon="chat-bubble-oval-left-ellipsis">Instagram <flux:badge size="sm" color="red">Beta</flux:badge></flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.platforms.telegram', session('workspace.uuid'))"
										href="{{ route('app.workspace.platforms.telegram', session('workspace.uuid')) }}" 
										icon="chat-bubble-oval-left-ellipsis">Telegram</flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.platforms.discord', session('workspace.uuid'))"
										href="{{ route('app.workspace.platforms.discord', session('workspace.uuid')) }}" 
										icon="chat-bubble-oval-left-ellipsis">Discord</flux:navlist.item>

									
									{{-- <flux:navlist.item href="{{ route('app.index') }}" icon="phone">SMS ?? (Soon)</flux:navlist.item> --}}
									
									<flux:navlist.item disabled href="#" icon="phone">WhatsApp <flux:badge size="sm" color="green">Soon</flux:badge></flux:navlist.item>
									

										</flux:navlist.group>

								<flux:navlist.group 
									expandable 
									heading="Modules"
									:expanded="request()->routeIs('app.workspace.modules.*', session('workspace.uuid'))">

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.modules.lead-collector', session('workspace.uuid'))"
										href="{{ route('app.workspace.modules.lead-collector', session('workspace.uuid')) }}" 
										icon="user-circle">Lead Collector</flux:navlist.item>
										
									<flux:navlist.item href="#" icon="document">Custom Forms <flux:badge size="sm" color="green">Soon</flux:badge></flux:navlist.item>
									<flux:navlist.item href="#" icon="shopping-cart">Product Selector <flux:badge size="sm" color="green">Soon</flux:badge></flux:navlist.item>
								
								</flux:navlist.group>

								<flux:navlist.group 
									expandable 
									heading="Agentic Tools"
									:expanded="request()->routeIs('app.workspace.agentic-tools.*', session('workspace.uuid'))">

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.agentic-tools.request-refunds', session('workspace.uuid'))"
										href="{{ route('app.workspace.agentic-tools.request-refunds', session('workspace.uuid')) }}" 
										icon="credit-card">Request Refunds</flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.agentic-tools.reports', session('workspace.uuid'))"
										href="{{ route('app.workspace.agentic-tools.reports', session('workspace.uuid')) }}" 
										icon="document-text">Reports</flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.agentic-tools.booking', session('workspace.uuid'))"
										href="{{ route('app.workspace.agentic-tools.booking', session('workspace.uuid')) }}" 
										icon="calendar">Booking</flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.agentic-tools.custom-api-integrations', session('workspace.uuid'))"
										href="{{ route('app.workspace.agentic-tools.custom-api-integrations', session('workspace.uuid')) }}" 
										icon="globe-alt">Custom API Integrations</flux:navlist.item>

									<flux:navlist.item 
										:current="request()->routeIs('app.workspace.agentic-tools.shopping-assistant', session('workspace.uuid'))"
										href="{{ route('app.workspace.agentic-tools.shopping-assistant', session('workspace.uuid')) }}" 
										icon="shopping-cart">Shopping Assistant</flux:navlist.item>
									
								</flux:navlist.group>
							
							</div>
						@endif

						<flux:menu class="w-[14.45rem]">
							@foreach($_workspaces as $workspace)
								<form action="{{ route('app.workspace.switch', $workspace->uuid) }}" method="POST">
									@csrf
									@method('PUT')

									<button class="group flex w-full items-center gap-3 rounded-lg p-1 hover:bg-zinc-800/5 dark:hover:bg-white/10" type="submit">
										@if ($workspace->icon)
											<span class="rounded-xs flex size-8 shrink-0 items-center justify-center">
												<img alt="" src="{{ $workspace->icon }}" />
											</span>
										@else
											<span class="rounded-xs flex size-8 shrink-0 items-center justify-center bg-zinc-400">
												{{ acronym($workspace->name) }}
											</span>
										@endif
										<span class="flex flex-col  items-start truncate text-sm text-zinc-500 group-hover:text-zinc-800 dark:text-white/80 dark:group-hover:text-white">
											<span class="font-medium">{{ $workspace->name }}</span>
											<span class="text-xs opacity-50">{{ $workspace->website }}</span>
										</span>

									</button>
								</form>
							@endforeach

							<flux:separator class="my-3" variant="subtle" />

							<flux:modal.trigger name="create-workspace">
								<flux:button class="w-full" icon-trailing="plus" size="sm" variant="primary">Create Workspace</flux:button>
							</flux:modal.trigger>
						</flux:menu>
					</flux:dropdown>
				@else
					<div class="p-4 flex flex-col items-center">
						<p class="text-center mb-4">You don't have any workspaces yet. Create a new workspace to get started.</p>
						<flux:modal.trigger name="create-workspace">
							<flux:button icon-trailing="plus" size="sm" variant="primary">Create Workspace</flux:button>
						</flux:modal.trigger>
					</div>
				@endif
			</flux:card>

			{{-- <flux:card>
				<flux:heading>
					Other shit
				</flux:heading>
				<flux:separator variant="subtle" class="my-1" />

				<flux:navlist.item href="{{ route('app.index') }}" icon="academic-cap">Training</flux:navlist.item>
				<flux:navlist.item href="{{ route('app.index') }}" icon="book-open">Documentation</flux:navlist.item>
				<flux:navlist.item href="{{ route('app.index') }}" icon="user-group">All Contacts</flux:navlist.item>
			</flux:card> --}}
		</div>
	</div>
</nav>

<livewire:app.workspace.workspace-create />