<div class="container mx-auto space-y-6">
	<flux:breadcrumbs>
		<flux:breadcrumbs.item wire:navigate href="{{ route('app.index') }}">Home</flux:breadcrumbs.item>
		<flux:breadcrumbs.item>Referrals</flux:breadcrumbs.item>
	</flux:breadcrumbs>

	<flux:card class="space-y-6">
		<div class="flex flex-col gap-4 md:flex-row md:items-center">
			<div>
				<flux:heading size="lg">Referrals</flux:heading>
				<flux:subheading>
					Share your referral link with friends and earn rewards.
				</flux:subheading>
			</div>

			<flux:spacer />

			<div class="flex gap-4 overflow-x-auto scroller">
				<flux:card>
					<flux:subheading class="flex items-center gap-1 whitespace-nowrap"><flux:icon icon="users" variant="micro" /> Total Referrers</flux:subheading>
					<flux:heading size="xl" class="mb-2">{{ $referrals->sum('referrers_count') }}</flux:heading>
				</flux:card>

				<flux:card>
					<flux:subheading class="flex items-center gap-1 whitespace-nowrap"><flux:icon icon="banknotes" variant="micro" /> Total Earnings</flux:subheading>
					<flux:heading size="xl" class="mb-2">${{ number_format($referrals->sum('total_commission'), 2) }}</flux:heading>
				</flux:card>

				<flux:card>
					<flux:subheading class="flex items-center gap-1 whitespace-nowrap"><flux:icon icon="banknotes" variant="micro" /> Total Pending</flux:subheading>
					<flux:heading size="xl" class="mb-2">${{ number_format($referrals->sum('total_pending_commission'), 2) }}</flux:heading>
				</flux:card>

				<flux:card>
					<flux:subheading class="flex items-center gap-1 whitespace-nowrap"><flux:icon icon="banknotes" variant="micro" /> Total Available</flux:subheading>
					<flux:heading size="xl" class="mb-2">${{ number_format($referrals->sum('total_available_commission'), 2) }}</flux:heading>
				</flux:card>
			</div>
		</div>

		<div class="[&>div>div:nth-of-type(1)]:rounded-t-none [&_table]:bg-white">
			<div class="rounded-t-lg border border-zinc-200 border-b-0  bg-white p-3">
				<div class="flex items-center gap-2">
					<flux:input wire:model.lazy="filter.search" icon="magnifying-glass" placeholder="Search by code" size="sm" class="sm:max-w-fit" />
					
					@if(data_get($this->filter, 'search'))
						<flux:button type="button" wire:click="set('filter.search', ''); $wire.resetPage()" size="sm" icon="x-mark" variant="ghost">Clear</flux:button>
					@endif

					<flux:spacer />

					<flux:modal.trigger name="add-referral">
						<flux:button variant="primary" size="sm" icon="plus">Add</flux:button>
					</flux:modal.trigger>
				</div>
			</div>

			<flux:table>
				<flux:table.columns>
					<flux:table.column>Referral Link</flux:table.column>
					<flux:table.column>Description</flux:table.column>
					<flux:table.column>Clicks</flux:table.column>
					<flux:table.column>Referrers</flux:table.column>
					<flux:table.column>CVR</flux:table.column>
					<flux:table.column>Commission Rate</flux:table.column>
					<flux:table.column>Commission Earned</flux:table.column>
					<flux:table.column>Created</flux:table.column>
				</flux:table.columns>
				<flux:table.rows>
					@forelse($referrals as $referral)
						<flux:table.row wire:key="{{ md5($referral->id) }}">
							<flux:table.cell>
								<flux:input value="{{ $referral->referral_link }}" size="sm" copyable />
							</flux:table.cell>
							<flux:table.cell>
								{{ $referral->description }}
							</flux:table.cell>
							<flux:table.cell>
								{{ $referral->clicks ?? 0 }}
							</flux:table.cell>
							<flux:table.cell>
								{{ $referral->referrers_count ?? 0 }}
							</flux:table.cell>
							<flux:table.cell>
								{{ $referral->conversion_rate }}%
							</flux:table.cell>
							<flux:table.cell>
								{{ $referral->commission_rate }}%
							</flux:table.cell>
							<flux:table.cell>
								${{ number_format($referral->total_commission, 2) }}
							</flux:table.cell>
							<flux:table.cell>
								{{ $referral->created_at->diffForHumans() }}
							</flux:table.cell>
						</flux:table.row>
					@empty
						<flux:table.row>
							<flux:table.cell colspan="8">
								<div class="flex flex-col gap-4 justify-center items-center py-10">
									<flux:heading size="lg">No referrals found</flux:heading>
									<img src="https://img.freepik.com/free-vector/detective-following-footprints-concept-illustration_114360-17638.jpg" class="size-60" />
								</div>
							</flux:table.cell>
						</flux:table.row>
					@endforelse
				</flux:table.rows>
			</flux:table>
		</div>
	</flux:card>

	<flux:modal name="add-referral">
		<form wire:submit="save" class="space-y-6 w-full max-w-[35rem]">
			<flux:heading size="lg">Referral Codes</flux:heading>
			<flux:subheading>
				Create a unique referral code to track individuals who visit through your link. You have the option to use the automatically generated code for convenience or customize your own to better suit your needs.
			</flux:subheading>

			<flux:input wire:model="form.code" label="Referral Code" description-trailing="* You can edit this field if you want to use your custom code" />
			<flux:input wire:model="form.description" label="Description" />

			<flux:button type="submit" variant="primary" icon="bookmark">Save</flux:button>
		</form>
	</flux:modal>
</div>
