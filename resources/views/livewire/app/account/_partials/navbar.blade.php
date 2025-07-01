<flux:navbar wire:ignore>
	<flux:navbar.item wire:navigate href="{{ route('app.account.index') }}" icon="user" :current="request()->routeIs('app.account.index')">Account</flux:navbar.item>
	<flux:navbar.item wire:navigate href="{{ route('app.account.sessions') }}" icon="server-stack" :current="request()->routeIs('app.account.sessions')">Sessions</flux:navbar.item>

	@if (!auth()->user()->pro)
		<flux:navbar.item wire:navigate href="{{ route('app.account.plans') }}" icon="server-stack" :current="request()->routeIs('app.account.plans')">Plans</flux:navbar.item>
	@endif
</flux:navbar>
