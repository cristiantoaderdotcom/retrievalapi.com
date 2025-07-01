<flux:card class="container mx-auto space-y-6">
	@includeIf('livewire.app.account._partials.navbar')

	<div>
		<flux:heading size="lg">Browser Sessions</flux:heading>
		<flux:subheading>If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.</flux:subheading>
	</div>

	@if (count($this->sessions) > 0)
		<div class="space-y-6">
			@foreach ($this->sessions as $session)
				<div class="flex items-center shadow-xs rounded-xs p-3">
					<div>
						@if ($session->agent->isDesktop())
							<flux:icon.computer-desktop />
						@else
							<flux:icon.device-phone-mobile />
						@endif
					</div>

					<div class="ms-3">
						<div class="text-sm text-zinc-600">
							{{ $session->agent->platform() ? $session->agent->platform() : __('Unknown') }} - {{ $session->agent->browser() ? $session->agent->browser() : __('Unknown') }}
						</div>

						<div>
							<div class="text-xs">
								<span class="text-zinc-500">{{ $session->ip_address }},</span>

								@if ($session->is_current_device)
									<span class="text-lime-600 font-semibold">{{ __('This device') }}</span>
								@else
									{{ __('last active') }} {{ $session->last_active }}
								@endif
							</div>
						</div>
					</div>
				</div>
			@endforeach

			<div>
				<flux:modal.trigger name="logout">
					<flux:button variant="primary">Log Out Other Browser Sessions</flux:button>
				</flux:modal.trigger>
			</div>
		</div>
	@endif

	<flux:modal name="logout" class="md:w-96 space-y-6">
		<div>
			<flux:heading size="lg">Log Out Other Browser Sessions</flux:heading>
			<flux:subheading>Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.</flux:subheading>
		</div>

		<form wire:submit="logout" class="space-y-6">
			<flux:input label="Confirm Password" wire:model="password" type="password" name="password" required viewable/>

			<div class="flex justify-end">
				<flux:button type="submit" variant="primary">Log Out Other Browser Sessions</flux:button>
			</div>
		</form>
	</flux:modal>
</flux:card>
