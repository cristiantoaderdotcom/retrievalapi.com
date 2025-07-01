<flux:card class="container mx-auto space-y-6">
	@includeIf('livewire.app.account._partials.navbar')

	<div>
		<flux:heading size="lg">Account details</flux:heading>
		<flux:subheading>Update your account's profile information and email address.</flux:subheading>
	</div>

	<form wire:submit="profile" class="space-y-6">
		<flux:field>
			<flux:label>Avatar</flux:label>
			<div class="flex items-center gap-2">
				@if (!empty($file))
					<img src="{{ $file->temporaryUrl() }}" class="size-9 rounded-lg" alt=""/>
				@elseif(data_get($this, 'avatar'))
					<img src="{{ data_get($this, 'avatar') }}" class="size-9 rounded-lg" alt=""/>
				@endif
				<flux:input type="file" wire:model="file"/>
			</div>
		</flux:field>

		<flux:input label="Name" wire:model="name" type="text" name="name" required autofocus autocomplete="name"/>
		<flux:input label="Email" wire:model="email" type="email" name="email" required autocomplete="email"/>

		<flux:button type="submit" variant="primary">Save changes</flux:button>
	</form>

	<flux:separator />

	<div>
		<flux:heading size="lg">Change password</flux:heading>
		<flux:subheading>Ensure your account is using a long, random password to stay secure.</flux:subheading>
	</div>

	<form wire:submit="updatePassword" class="space-y-6">
		<flux:input label="Current Password" wire:model="current_password" type="password" name="current_password" required viewable autocomplete="new-password"/>
		<flux:input label="New Password" wire:model="password" type="password" name="password" required viewable autocomplete="new-password"/>
		<flux:input label="Confirm Password" wire:model="password_confirmation" type="password" name="password_confirmation" required viewable autocomplete="new-password"/>

		<flux:button type="submit" variant="primary">Update Password</flux:button>
	</form>
</flux:card>
