@section('title', 'Reset your password')

<div class="space-y-6">
	<div>
		<flux:heading size="lg">Reset Your Password</flux:heading>
		<flux:subheading>Enter your new password below to regain access to your account.</flux:subheading>
	</div>

	<form wire:submit.prevent="store" class="space-y-6">
		<input wire:model="token" type="hidden"/>
		<flux:input label="Email" wire:model="email" type="email" name="email" required autofocus autocomplete="email"/>
		<flux:input label="Password" wire:model="password" type="password" name="password" required viewable/>
		<flux:input label="Confirm Password" wire:model="password_confirmation" type="password" name="password_confirmation" required viewable/>

		<flux:button type="submit" variant="primary" class="w-full">Reset Password</flux:button>
	</form>
</div>