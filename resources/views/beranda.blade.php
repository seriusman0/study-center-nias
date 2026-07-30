@extends('layouts.app')
@section('title', 'Beranda - Study Center Nias')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 space-y-8">

    {{-- 1. QR Code --}}
    <div class="bg-white rounded-2xl shadow-sc-2 border border-sc-line p-6 text-center">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-1">QR Code Absensi</h2>
        <p class="text-sm text-sc-ink-500 mb-4">Tunjukkan ke mentor saat absensi</p>
        <div class="mx-auto" style="width:180px;height:180px">{!! $qrHtml !!}</div>
        <p class="text-xs text-sc-ink-400 mt-3">{{ $user->name }}</p>
    </div>

    {{-- 2. Jurnal Hari Ini --}}
    @php
        $jurnalUrl  = auth()->user()->hasRole('college') ? route('college-jurnal.index') : route('jurnal.index');
        $plDone     = (bool) $todayEntry?->pl_checked;
        $pbDone     = (bool) $todayEntry?->pb_checked;
        $lifePct    = $totalLifeItems > 0 ? round($lifeChecksToday / $totalLifeItems * 100) : 0;
        $hasAnyProgress = $todayEntry || $lifeChecksToday > 0;
    @endphp
    <div class="rounded-2xl shadow-sc-2 border border-sc-line overflow-hidden">

        {{-- Header gradient --}}
        <div class="bg-gradient-to-br from-sc-teal-700 to-sc-teal-500 px-6 pt-7 pb-5">
            <p class="text-white/80 text-xs font-medium uppercase tracking-widest mb-1">Jurnal Hari Ini</p>
            <h2 class="text-white text-xl font-bold leading-tight">Jadwal Baca Alkitab</h2>
            @if($bibleItem)
                <p class="text-white/90 text-xs font-semibold mt-2">Hari ke-{{ $dayNo }}</p>
            @endif
        </div>

        <div class="bg-white px-6 pb-6">
            @if($bibleItem)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-8">
                {{-- PL --}}
                <div class="rounded-xl border border-gray-200 shadow-sm p-4 flex items-start gap-3 transition
                    {{ $plDone ? 'bg-sc-teal-50 border-sc-teal-300' : 'bg-white' }}">
                    <div class="mt-0.5 w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center
                        {{ $plDone ? 'bg-sc-teal-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                        @if($plDone)
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Perjanjian Lama</p>
                        <p class="text-sm font-semibold text-sc-ink-900 mt-1 leading-tight">{{ $bibleItem->pl_text ?: '—' }}</p>
                    </div>
                </div>
                {{-- PB --}}
                <div class="rounded-xl border border-gray-200 shadow-sm p-4 flex items-start gap-3 transition
                    {{ $pbDone ? 'bg-sc-teal-50 border-sc-teal-300' : 'bg-white' }}">
                    <div class="mt-0.5 w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center
                        {{ $pbDone ? 'bg-sc-teal-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                        @if($pbDone)
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Perjanjian Baru</p>
                        <p class="text-sm font-semibold text-sc-ink-900 mt-1 leading-tight">{{ $bibleItem->pb_text ?: '—' }}</p>
                    </div>
                </div>
            </div>
            @else
            <div class="mt-6 rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 text-center text-sm text-gray-400">
                Porsi baca Alkitab belum tersedia untuk hari ini.
            </div>
            @endif

            {{-- Jadwal Kehidupan progress --}}
            @if($totalLifeItems > 0)
            <div class="mt-6 bg-gray-50 rounded-xl p-4">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-sm font-semibold text-sc-ink-700">Jadwal Kehidupan</span>
                    <span class="text-sm font-bold {{ $lifePct === 100 ? 'text-sc-teal-600' : 'text-sc-ink-500' }}">
                        {{ $lifeChecksToday }}<span class="text-gray-400 font-normal">/{{ $totalLifeItems }}</span>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-500
                        {{ $lifePct === 100 ? 'bg-sc-teal-500' : 'bg-sc-teal-400' }}"
                         style="width: {{ $lifePct }}%"></div>
                </div>
                @if($lifePct === 100)
                <p class="text-xs text-sc-teal-600 font-semibold mt-2">Semua selesai hari ini!</p>
                @endif
            </div>
            @endif

            {{-- CTA --}}
            <div class="pt-6">
                @if(!$hasAnyProgress)
                <a href="{{ $jurnalUrl }}"
                   class="flex items-center justify-center gap-2 w-full py-3 bg-sc-teal-600 hover:bg-sc-teal-700 text-white font-semibold text-sm rounded-xl transition">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Isi Jurnal Sekarang
                </a>
                @else
                <a href="{{ $jurnalUrl }}"
                   class="flex items-center justify-center gap-2 w-full py-3 border border-sc-teal-300 text-sc-teal-700 hover:bg-sc-teal-50 font-semibold text-sm rounded-xl transition">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Lihat Jurnal Lengkap
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. Blog Cabang --}}
    @if($blogs->count())
    <div class="bg-white rounded-2xl shadow-sc-2 border border-sc-line p-6">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-4">Artikel Terbaru</h2>
        <div class="space-y-3">
            @foreach($blogs as $blog)
            <a href="{{ route('blog.show', $blog->slug) }}"
               class="flex gap-3 p-3 rounded-xl hover:bg-sc-teal-50 transition group">
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-sc-ink-900 group-hover:text-sc-teal-700 text-sm leading-snug line-clamp-2">{{ $blog->title }}</h3>
                    <p class="text-xs text-sc-ink-400 mt-1">{{ $blog->published_at->format('d M Y') }} · {{ $blog->cabang?->nama ?? 'Study Center' }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 4. Photo Slider --}}
    <div class="bg-white rounded-2xl shadow-sc-2 border border-sc-line p-6">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-4">Galeri Kegiatan</h2>
        @if($photos->isEmpty())
        <div class="flex flex-col items-center justify-center py-8">
            <img src="{{ asset('assets/img/logo.png') }}" class="w-20 h-20 object-contain opacity-40 mb-3" alt="Logo">
            <p class="text-sm text-sc-ink-400">Belum ada foto kegiatan</p>
        </div>
        @else
        <div class="swiper galeri-swiper rounded-xl overflow-hidden">
            <div class="swiper-wrapper">
                @foreach($photos as $foto)
                <div class="swiper-slide">
                    <img src="{{ asset('storage/' . $foto) }}" alt="Foto Kegiatan"
                         class="w-full h-56 object-cover">
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
@if($photos->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
new Swiper('.galeri-swiper', {
    loop: true,
    autoplay: { delay: 2000, disableOnInteraction: false },
    pagination: { el: '.swiper-pagination', clickable: true },
});
</script>
@endif
@endpush
