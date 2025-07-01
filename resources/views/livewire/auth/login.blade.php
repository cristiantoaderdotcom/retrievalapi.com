@section('title', 'Sign in to your account')

<div class="space-y-6">
	<div>
		<flux:heading size="lg" class="text-zinc-900">Welcome back!</flux:heading>
		<flux:subheading class="text-zinc-600">Sign in to your account to continue building amazing AI chatbots.</flux:subheading>
	</div>

	<form wire:submit.prevent="store" class="space-y-6">
		<flux:input label="Email" wire:model="email" type="email" name="email" :value="old('email')" required autofocus
					autocomplete="email" class="text-zinc-900" />

		<flux:field>
			<flux:label class="flex justify-between text-zinc-700">
				Password

				@if (Route::has('password.request'))
					<flux:link wire:navigate href="{{ route('password.request') }}" class="text-xs ml-2 text-green-600 hover:text-green-700"> Forgot password?</flux:link>
				@endif
			</flux:label>

			<flux:input type="password" wire:model="password" placeholder="Your password" name="password" autocomplete="current-password" required viewable class="text-zinc-900" />
			<flux:error name="password" />
		</flux:field>

		<flux:checkbox wire:model="remember" name="remember" label="Remember me" class="text-zinc-700" />

		<div class="space-y-6">
			<flux:button type="submit" variant="primary" class="w-full bg-green-600 hover:bg-green-700 focus:ring-green-500">Log in</flux:button>
			<flux:subheading class="text-center text-zinc-600">
				Don't have an account?
				<flux:link wire:navigate href="{{ route('register') }}" class="text-green-600 hover:text-green-700">Sign up for free</flux:link>
			</flux:subheading>
		</div>
	</form>
</div>
