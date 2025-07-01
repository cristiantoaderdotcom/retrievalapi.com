@section('title', 'Register your account')

<div class="space-y-6">
	<div>
		<flux:heading size="lg" class="text-zinc-900">Let's create your account!</flux:heading>
		<flux:subheading class="text-zinc-600">Join us and start building amazing AI chatbots for your website.</flux:subheading>
	</div>

	<flux:button class="w-full bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700" icon="google" href="{{ route('socialite.redirect', 'google') }}">Continue with Google</flux:button>

	<flux:separator text="or" class="text-zinc-500" />

	<form wire:submit.prevent="store" class="space-y-6 ">
		<flux:input label="Name" wire:model="name" type="text" name="name"  required autofocus autocomplete="none" class="text-zinc-900"/>
		<flux:input label="Email (Validation will be sent to this email)" wire:model="email" type="email" name="email"  required autocomplete="none" class="text-zinc-900"/>
		<flux:input label="Password" wire:model="password" type="password" name="password" required viewable autocomplete="current-password" class="text-zinc-900"/>
		<flux:input label="Confirm Password" wire:model="password_confirmation" type="password" name="password_confirmation" required viewable autocomplete="new-password" class="text-zinc-900"/>

		<div class="space-y-6">
			<flux:button type="submit" variant="primary" class="w-full bg-green-600 hover:bg-green-700 focus:ring-green-500">Create Account</flux:button>
			<flux:subheading class="text-center text-zinc-600">
				Already have an account?
				<flux:link wire:navigate href="{{ route('login') }}" class="text-green-600 hover:text-green-700">Log in</flux:link>
			</flux:subheading>
		</div>
	</form>
</div>