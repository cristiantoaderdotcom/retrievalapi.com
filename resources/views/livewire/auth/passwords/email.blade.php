@section('title', 'Forgot your password?')

<div class="space-y-6">
	<div>
		<flux:heading size="lg">Forgot your password?</flux:heading>
		<flux:subheading>Have you forgotten the password? No problem. Just enter your email address and we'll email you a password reset link that will allow you to choose a new one.</flux:subheading>
	</div>

	<form wire:submit.prevent="store" class="space-y-6">
		<flux:input label="Email" wire:model="email" type="email" name="email" :value="old('email')" required autofocus
					autocomplete="email" />

		<flux:button type="submit" variant="primary" icon="lock-open" class="w-full">Recover</flux:button>
		
	</form>
</div>