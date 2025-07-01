@props([
    'variant' => 'outline',
])

@php
	$classes = Flux::classes('shrink-0')
		->add(match($variant) {
			'outline' => '[:where(&)]:size-6',
			'solid' => '[:where(&)]:size-6',
			'mini' => '[:where(&)]:size-5',
			'micro' => '[:where(&)]:size-4',
		});
@endphp

<svg {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="32"
	 height="32" fill="currentColor" viewBox="0 0 256 256">
	<path
		d="M104,60A12,12,0,1,1,92,48,12,12,0,0,1,104,60Zm60,12a12,12,0,1,0-12-12A12,12,0,0,0,164,72ZM92,116a12,12,0,1,0,12,12A12,12,0,0,0,92,116Zm72,0a12,12,0,1,0,12,12A12,12,0,0,0,164,116ZM92,184a12,12,0,1,0,12,12A12,12,0,0,0,92,184Zm72,0a12,12,0,1,0,12,12A12,12,0,0,0,164,184Z"></path>
</svg>
