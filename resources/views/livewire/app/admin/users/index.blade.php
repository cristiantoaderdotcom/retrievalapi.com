<div>
    <flux:card class="space-y-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item wire:navigate href="{{ route('app.admin.users.index') }}">Admin</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Users</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="[&>div>div:nth-of-type(1)]:rounded-t-none [&_table]:bg-white">
            <div class="rounded-t-lg border border-zinc-200 border-b-0  bg-white p-3">
                <div class="flex items-center gap-2">
                    <div class="max-sm:hidden flex items-baseline gap-3">
                        <flux:heading size="lg">Users</flux:heading>
                        <flux:text>({{ $users->total() }})</flux:text>
                    </div>
                    <flux:spacer />

                    <flux:button size="sm" wire:click="create">Create User</flux:button>
                </div>
            </div>

            <flux:table :paginate="$users">
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Standard</flux:table.column>
                    <flux:table.column>Pro</flux:table.column>
                    <flux:table.column>Monthly Messages</flux:table.column>
                    <flux:table.column>Train Context Limit</flux:table.column>
                    <flux:table.column>Created</flux:table.column>
                    <flux:table.column class="flex justify-end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($users as $user)
                        <flux:table.row>
                            <flux:table.cell class="flex items-center gap-2">
                                {{ $user->name }}
                                
                                @foreach($user->roles as $role)
                                    <flux:badge size="sm">{{ $role->name }}</flux:badge>
                                @endforeach
                            </flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$user->standard ? 'lime' : 'amber'">{{ $user->standard ? 'Yes' : 'No' }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$user->pro ? 'lime' : 'amber'">{{ $user->pro ? 'Yes' : 'No' }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $user->messages_limit }}</flux:table.cell>
                            <flux:table.cell>{{ $user->context_limit }}</flux:table.cell>
                            <flux:table.cell>{{ $user->created_at->format('M j, g:i A') }}</flux:table.cell>

                            <flux:table.cell class="sticky right-0">
                                <div class="flex justify-end">
                                    <flux:dropdown position="bottom" align="end" offset="-15">
                                        <flux:button tooltip="Manage" variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                        <flux:menu>
                                            <flux:menu.item icon="pencil" wire:click="edit({{ $user->id }})">Edit</flux:menu.item>
                                            <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $user->id }})" wire:confirm="Are you sure you want to delete this user?">Delete</flux:menu.item>
                                            <flux:menu.separator variant="subtle" />
                                            <flux:menu.item icon="user" wire:click="login({{ $user->id }})">Login as user</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

	<flux:modal name="user-edit" class="w-full md:max-w-xl scroller">
		<form wire:submit="save" class="space-y-6">
				<div>
					<flux:heading size="lg">Update user</flux:heading>
					<flux:subheading>Make changes to the user's details.</flux:subheading>
				</div>

				<flux:input wire:model="form.name" label="Name" placeholder="Name" />
				<flux:input wire:model="form.email" label="Email" placeholder="Email" />

				<flux:switch wire:model.boolean="form.change_password" label="Change Password" description="Toggle to change the user's password" />

				<div x-show="$wire.form.change_password" class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<flux:input wire:model="form.password" label="Password" placeholder="Password" />
					<flux:input wire:model="form.confirm_password" label="Confirm password" placeholder="Confirm password" />
				</div>
				
				<flux:separator variant="subtle" />

                <flux:switch wire:model.boolean="form.standard" label="Standard" description="If the user is a standard user or not" />
                <flux:switch wire:model.boolean="form.pro" label="Pro" description="If the user is a pro user or not" />

				<flux:separator variant="subtle" />

				<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<flux:input wire:model="form.messages_limit" label="Monthly Messages" placeholder="Monthly Messages" />
					<flux:input wire:model="form.context_limit" label="Train Context Limit" placeholder="Train Context Limit" />
				</div>

				<flux:separator variant="subtle" />
				
				
                <flux:switch wire:model.boolean="form.email_verified_at" label="Email verified" description="If the user's email is verified or not" />
                <flux:switch wire:model.boolean="form.status" label="Status" description="If the user is active or not" />

                <flux:button type="submit" variant="primary">Save changes</flux:button>
		</form>
	</flux:modal>
</div>