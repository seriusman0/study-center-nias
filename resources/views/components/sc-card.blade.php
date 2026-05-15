@props([
    'title' => null,
    'eyebrow' => null,
    'padding' => 'p-5',
])
<div {{ $attributes->merge(['class' => "bg-white rounded-xl shadow-sc-2 border border-sc-line {$padding}"]) }}>
    @if($eyebrow)
        <div class="sc-eyebrow mb-2">{{ $eyebrow }}</div>
    @endif
    @if($title)
        <h3 class="text-lg font-bold text-sc-ink-900 mb-3">{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>
