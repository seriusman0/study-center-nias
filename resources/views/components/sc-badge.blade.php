@props(['role' => null, 'tone' => 'teal'])
@php
    $tones = [
        'teal'   => 'bg-sc-teal-100 text-sc-teal-700',
        'orange' => 'bg-sc-orange-100 text-sc-orange-700',
        'yellow' => 'bg-sc-yellow-100 text-sc-yellow-700',
        'gray'   => 'bg-sc-line-soft text-sc-ink-700',
    ];
    $roleTones = [
        'admin'     => 'teal',
        'student'   => 'teal',
        'mentor'    => 'orange',
        'fulltimer' => 'yellow',
    ];
    $key = $role ? ($roleTones[strtolower($role)] ?? $tone) : $tone;
    $cls = $tones[$key] ?? $tones['teal'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {$cls}"]) }}>
    {{ $slot }}
</span>
