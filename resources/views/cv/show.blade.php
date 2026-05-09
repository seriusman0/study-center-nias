@extends('layouts.app')

@section('title', 'CV ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-[#1e3a5f]">CV {{ $user->name }}</h1>
        <button onclick="window.print()"
                class="px-4 py-2 bg-[#c9a84c] text-[#1e3a5f] rounded-lg font-semibold hover:bg-yellow-400 text-sm">
            🖨️ Cetak / Download
        </button>
    </div>

    <div id="cv-content" class="bg-white rounded-2xl shadow p-8 space-y-8 print:shadow-none print:rounded-none">
        {{-- Header --}}
        <div class="flex items-center gap-6 border-b pb-6">
            <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=100&background=1e3a5f&color=fff' }}"
                 class="w-24 h-24 rounded-full object-cover" alt="{{ $user->name }}">
            <div>
                <h2 class="text-2xl font-bold text-[#1e3a5f]">{{ $user->name }}</h2>
                <p class="text-gray-600 capitalize">{{ $user->role?->name }}</p>
                @if($user->cabang)
                <p class="text-gray-500 text-sm">{{ $user->cabang->nama }}</p>
                @endif
                @if($user->bio)
                <p class="text-gray-700 mt-2 text-sm">{{ $user->bio }}</p>
                @endif
            </div>
        </div>

        @php $cv = $user->cvData; @endphp

        {{-- Pendidikan --}}
        @if($cv && count($cv->pendidikan ?? []))
        <div>
            <h3 class="font-bold text-[#1e3a5f] border-b border-[#c9a84c] pb-1 mb-3">Pendidikan</h3>
            @foreach($cv->pendidikan as $edu)
            <div class="mb-2">
                <p class="font-semibold">{{ $edu['institusi'] ?? '' }}</p>
                <p class="text-sm text-gray-600">{{ $edu['jenjang'] ?? '' }} · {{ $edu['tahun_lulus'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Pengalaman --}}
        @if($cv && count($cv->pengalaman ?? []))
        <div>
            <h3 class="font-bold text-[#1e3a5f] border-b border-[#c9a84c] pb-1 mb-3">Pengalaman</h3>
            @foreach($cv->pengalaman as $exp)
            <div class="mb-3">
                <p class="font-semibold">{{ $exp['posisi'] ?? '' }}</p>
                <p class="text-xs text-gray-400">{{ $exp['tahun'] ?? '' }}</p>
                @if(!empty($exp['deskripsi']))
                <p class="text-sm text-gray-600 mt-1">{{ $exp['deskripsi'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Keterampilan --}}
        @if($cv && count($cv->keterampilan ?? []))
        <div>
            <h3 class="font-bold text-[#1e3a5f] border-b border-[#c9a84c] pb-1 mb-3">Keterampilan</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($cv->keterampilan as $skill)
                <span class="bg-[#1e3a5f]/10 text-[#1e3a5f] px-3 py-1 rounded-full text-sm">{{ $skill }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Portofolio --}}
        @if($cv && $cv->portofolio)
        <div>
            <h3 class="font-bold text-[#1e3a5f] border-b border-[#c9a84c] pb-1 mb-3">Portofolio</h3>
            <p class="text-gray-700 text-sm whitespace-pre-line">{{ $cv->portofolio }}</p>
        </div>
        @endif

        {{-- Kontak --}}
        @if($user->socialLinks->count())
        <div>
            <h3 class="font-bold text-[#1e3a5f] border-b border-[#c9a84c] pb-1 mb-3">Kontak</h3>
            @foreach($user->socialLinks as $link)
            <p class="text-sm text-gray-600 capitalize">{{ $link->platform }}: {{ $link->value }}</p>
            @endforeach
        </div>
        @endif
    </div>
</div>

<style>
@media print {
    nav, footer, button { display: none !important; }
    body { background: white; }
    #cv-content { margin: 0; padding: 2rem; }
}
</style>
@endsection
