@php
    $classes = Flux::classes()
		->add('bg-zinc-100 dark:bg-zinc-900 border-b border-zinc-800/10 dark:border-white/20')
		;
@endphp

<thead {{ $attributes->class($classes) }} data-flux-columns>
    <tr {{ isset($tr) ? $tr->attributes : '' }}>
        {{ $tr ?? $slot }}
    </tr>
</thead>
