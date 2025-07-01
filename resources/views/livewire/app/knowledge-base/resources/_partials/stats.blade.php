@if ($polling)
	<div wire:poll.8s class="grid grid-cols-3 gap-6">
		<div class="flex items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 py-3">
			<div class="flex items-center gap-2">
				<span class="text-sm text-amber-800">Processing</span>
				<flux:badge color="amber">{{ $this->processing }}</flux:badge>
			</div>
		</div>
		<div class="flex items-center justify-center rounded-2xl border border-lime-200 bg-lime-50 py-3">
			<div class="flex items-center gap-2">
				<span class="text-sm text-lime-800">Processed</span>
				<flux:badge color="lime">{{ $this->processed }}</flux:badge>
			</div>
		</div>
		<div class="flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 py-3">
			<div class="flex items-center gap-2">
				<span class="text-sm text-red-800">Failed</span>
				<flux:badge color="red">{{ $this->failed }}</flux:badge>
			</div>
		</div>
	</div>
@endif
