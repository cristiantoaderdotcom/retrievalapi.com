@section('title', 'Register your account')

<div class="space-y-6">
	<div>
		<flux:heading size="lg">Verify email address</flux:heading>
		<flux:subheading>Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.</flux:subheading>
	</div>

	<form wire:submit.prevent="store">
		<flux:button type="submit" variant="primary" class="w-full">Resend Email Verification</flux:button>
	</form>

	<form method="POST" action="{{ route('logout') }}">
		@csrf
		<flux:button type="submit" variant="danger" class="w-full">Log Out</flux:button>
	</form>
</div>