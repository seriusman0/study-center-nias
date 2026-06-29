@props([
    'variant' => 'primary',
    'type' => 'button',
    'loading' => false,
    'icon' => null,
])
@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl px-4 py-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-sc-teal-600 disabled:opacity-50 disabled:cursor-not-allowed';
    $styles = [
        'primary'   => 'bg-sc-teal-600 hover:bg-sc-teal-700 text-white shadow-sc-1 hover:shadow-sc-2',
        'secondary' => 'border border-sc-teal-600 text-sc-teal-600 hover:bg-sc-teal-50',
        'accent'    => 'bg-sc-orange-500 hover:bg-sc-orange-600 text-white shadow-sc-1 hover:shadow-sc-2',
        'ghost'     => 'text-sc-ink-700 hover:bg-sc-line-soft',
        'danger'    => 'bg-red-600 hover:bg-red-700 text-white',
    ];
    $cls = $base . ' ' . ($styles[$variant] ?? $styles['primary']);
@endphp
<button {{ $attributes->merge(['type' => $type, 'class' => $cls]) }}>
    @if($loading)
        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/>
            <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="3" class="opacity-75"/>
        </svg>
    @endif
    {{ $slot }}
</button>
