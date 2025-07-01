<div class="lg:-ml-4 ml-[0.50rem] my-3 flex items-center gap-6">
	<button  x-show="collapsed || !$store.screen.lg" x-cloak type="button" x-on:click="collapsed = !collapsed; localStorage.setItem('navbar', collapsed);">
		<x-icons.menu class="size-6"/>
	</button>

	<div class="flex h-12 flex-1 items-center justify-between">
		<div class="flex items-center gap-5"
			x-show="($store.screen.lg && collapsed) || (!$store.screen.lg && !collapsed)">
			<a href="{{ route('app.index') }}" class="block">
				<img loading="lazy" class="h-12" src="{{ asset('assets/images/logo/logo.png') }}" width="auto" height="48" alt="IframeAI Logo" />
			</a>
		</div>

		<!-- Right side content - pushed to the right -->
		<div class="flex items-center gap-4 ml-auto">
			<!-- Account Credits -->
			<div class="flex items-center gap-4 bg-white/50 dark:bg-zinc-800/50 px-4 py-2 rounded-lg shadow-sm">
				
				<div class="flex items-center gap-2">
					<flux:icon.bolt class="size-4 text-amber-500" />
					<span class="text-sm font-medium">Workspaces:</span>
					<span class="font-semibold text-amber-600">{{ number_format(auth()->user()->workspaces()->count()) }}</span>
				</div>

				<div class="flex items-center gap-2">
					<flux:icon.bolt class="size-4 text-amber-500" />
					<span class="text-sm font-medium">Messages left:</span>
					<span class="font-semibold text-amber-600">{{ number_format(auth()->user()->messages_limit) }}</span>
				</div>
				<div class="hidden md:flex items-center gap-2">
					<flux:icon.document-text class="size-4 text-blue-500" />
					<span class="text-sm font-medium">KB Chars left:</span>
					<span class="font-semibold text-blue-600">{{ number_format(auth()->user()->context_limit) }}</span>
				</div>
				{{-- <flux:modal.trigger name="recharge-tokens">
					<flux:button class="!py-1 !px-2" icon="plus-circle" size="xs" variant="primary">
						Recharge
					</flux:button>
				</flux:modal.trigger> --}}
			</div>

			<!-- User Dropdown -->
			<flux:dropdown align="end" class="flex" gap="5" position="bottom">
				<button class="group flex items-center gap-3 rounded-lg p-1 hover:bg-zinc-800/5 dark:hover:bg-white/10" type="button">
					@if (!empty(auth()->user()->avatar))
						<img alt="" class="size-10 rounded-lg" src="{{ auth()->user()->avatar }}" />
					@else
						<span class="rounded-xs flex size-10 shrink-0 items-center justify-center bg-zinc-400">
							{{ acronym(auth()->user()->name) }}
						</span>
					@endif
					<span class="hidden md:flex flex-col items-start truncate text-sm group-hover:text-zinc-800 dark:text-white/80 dark:group-hover:text-white">
						<span class="font-medium">{{ auth()->user()->name }}</span>
						<span class="text-xs opacity-70">{{ auth()->user()->plan }}</span>
					</span>
					<span class="flex size-8 items-center justify-center">
						<flux:icon.chevron-down class="text-zinc-400 group-hover:text-zinc-800 dark:text-white/80 dark:group-hover:text-white" variant="micro" />
					</span>
				</button>

				<flux:menu class="w-[14.45rem]">
					@role('admin')
						<flux:navlist.item badge-color="red" badge="Admin" href="{{ route('app.admin.users.index') }}" icon="user-group">
							Users
						</flux:navlist.item>

						<flux:separator class="my-3" variant="subtle" />
					@endrole

					<flux:navlist.item href="{{ route('app.account.index') }}" icon="user">
						Account
					</flux:navlist.item>

					<flux:navlist.item href="{{ route('app.account.referrals') }}" icon="user-group">Referrals</flux:navlist.item>

					<flux:navlist.item href="mailto:support@iframeai.com" icon="chat-bubble-bottom-center-text">Support</flux:navlist.item>

					<flux:separator class="my-3" variant="subtle" />

					<flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
						<flux:radio value="light" icon="sun" />
						<flux:radio value="dark" icon="moon" />
						<flux:radio value="system" icon="computer-desktop" />
					</flux:radio.group>

					<flux:separator class="my-3" variant="subtle" />

					<form action="{{ route('logout') }}" method="POST">
						@csrf
						<flux:navlist.item icon="arrow-right-start-on-rectangle" type="submit">Logout</flux:navlist.item>
					</form>
				</flux:menu>
			</flux:dropdown>
		</div>
	</div>
</div>

<!-- Include the recharge tokens component since we're using it in the topbar now -->
<livewire:app.account.recharge-tokens />