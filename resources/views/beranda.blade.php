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

    {{-- 2. Journal Progress Today --}}
    <div class="bg-white rounded-2xl shadow-sc-2 border border-sc-line p-6">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-4">Jurnal Hari Ini</h2>
        @if(!$todayEntry && $lifeChecksToday === 0)
        <div class="text-center py-4">
            <p class="text-sc-ink-500 text-sm mb-3">Belum ada catatan jurnal hari ini.</p>
            <a href="{{ route('jurnal.index') }}" class="inline-block px-5 py-2 bg-sc-teal-600 text-white rounded-xl text-sm font-semibold hover:bg-sc-teal-700 transition">Isi Jurnal Sekarang</a>
        </div>
        @else
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-sc-teal-50 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-sc-teal-700">{{ $todayEntry?->pl_checked ? '✓' : '—' }}</div>
                <div class="text-xs text-sc-ink-500 mt-1">Pembelajaran</div>
            </div>
            <div class="bg-sc-teal-50 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-sc-teal-700">{{ $todayEntry?->pb_checked ? '✓' : '—' }}</div>
                <div class="text-xs text-sc-ink-500 mt-1">Pembiasaan</div>
            </div>
        </div>
        @if($totalLifeItems > 0)
        <div class="bg-gray-50 rounded-xl p-4">
            <div class="flex justify-between text-sm mb-2">
                <span class="text-sc-ink-700 font-medium">Kehidupan Sehari-hari</span>
                <span class="text-sc-teal-700 font-bold">{{ $lifeChecksToday }}/{{ $totalLifeItems }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-sc-teal-600 h-2 rounded-full transition-all"
                     style="width: {{ $totalLifeItems > 0 ? round($lifeChecksToday/$totalLifeItems*100) : 0 }}%"></div>
            </div>
        </div>
        @endif
        <div class="mt-3 text-right">
            <a href="{{ route('jurnal.index') }}" class="text-sm text-sc-teal-600 hover:underline font-medium">Lihat jurnal lengkap →</a>
        </div>
        @endif
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
