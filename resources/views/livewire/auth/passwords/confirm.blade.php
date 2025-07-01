@section('title', 'Confirm your password')

<div class="space-y-6">
	<div>
		<flux:heading size="lg">Confirm your password</flux:heading>
		<flux:subheading>Please confirm your password before continuing</flux:subheading>
	</div>

	<form wire:submit.prevent="store" class="space-y-6">
		<flux:input label="Password" wire:model="password" type="password" name="password" required viewable autofocus autocomplete="current-password"/>

		<div class="space-y-6">
			<flux:button type="submit" variant="primary" icon="lock-open" class="w-full">Confirm Password</flux:button>
			<flux:subheading class="text-center">
				<flux:link href="{{ route('password.request') }}">Forgot your password?</flux:link>
			</flux:subheading>
		</div>
	</form>
</div>