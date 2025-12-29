@props([
    'heading' => null,
])

<div {{ $attributes->class('rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6') }}>
    @if($heading)
        <h3 class="text-lg font-semibold mb-4">{{ $heading }}</h3>
    @endif
    
    {{ $slot }}
</div>
