@extends('layouts.app')

@section('title', 'Kartu Nama ' . $user->name)

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-[#1e3a5f]">Kartu Nama</h1>
        <button onclick="window.print()"
                class="px-4 py-2 bg-[#c9a84c] text-[#1e3a5f] rounded-lg font-semibold hover:bg-yellow-400 text-sm">
            🖨️ Cetak / Download
        </button>
    </div>

    {{-- Business Card --}}
    <div id="kartu" class="w-full max-w-sm mx-auto aspect-[1.75] bg-[#1e3a5f] rounded-xl shadow-xl p-6 flex flex-col justify-between">
        <div>
            <p class="text-[#c9a84c] font-bold text-lg">Study Center</p>
            <div class="w-16 h-1 bg-[#c9a84c] mt-1 mb-4"></div>
            <p class="text-white font-bold text-xl">{{ $user->name }}</p>
            <p class="text-white/70 text-sm capitalize">{{ $user->role?->name }}</p>
            @if($user->cabang)
            <p class="text-white/50 text-xs">{{ $user->cabang->nama }}</p>
            @endif
        </div>
        <div class="space-y-1">
            @php
            $wa = $user->socialLinks->firstWhere('platform', 'whatsapp');
            $email = $user->socialLinks->firstWhere('platform', 'email');
            @endphp
            @if($wa)
            <p class="text-white/70 text-xs">📱 {{ $wa->value }}</p>
            @endif
            @if($email)
            <p class="text-white/70 text-xs">✉️ {{ $email->value }}</p>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    nav, footer, button, h1, .flex.justify-between { display: none !important; }
    body { background: white; }
    #kartu {
        margin: 2cm auto;
        width: 8.5cm;
        border-radius: 0.5cm;
    }
}
</style>
@endsection
