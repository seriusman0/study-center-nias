@extends('layouts.app')

@section('title', 'Study Center Nias')

@section('content')
{{-- Hero --}}
<section class="bg-gradient-to-br from-sc-teal-700 to-sc-teal-600 text-white py-20 px-4 relative overflow-hidden">
    <svg viewBox="0 0 200 200" class="absolute -right-10 -top-10 w-72 opacity-10 pointer-events-none" aria-hidden="true">
        <path d="M60,110 L100,20 L140,110 Z" fill="#e0c020" />
        <rect x="50" y="120" width="100" height="20" fill="#e0c020" />
        <rect x="50" y="144" width="100" height="20" fill="#f19121" />
    </svg>
    <div class="max-w-4xl mx-auto text-center relative">
        <p class="sc-eyebrow text-sc-yellow-300 mb-3">KOMUNITAS BELAJAR NIAS</p>
        <h1 class="font-display text-4xl md:text-6xl mb-4 leading-tight">
            Study Center <span class="text-sc-yellow-300">Nias</span>
        </h1>
        <p class="text-white/85 text-lg mb-8 max-w-2xl mx-auto leading-relaxed">
            Rumah kedua remaja Nias. Tempat belajar, bertumbuh.
        </p>
        <div class="flex gap-3 justify-center flex-wrap">
            <a href="{{ route('blog.index') }}"
               class="px-6 py-3 bg-sc-orange-500 text-white font-semibold rounded-lg hover:bg-sc-orange-600 transition shadow-sc-2">
                Baca Blog
            </a>
            @guest
            <a href="{{ route('register') }}"
               class="px-6 py-3 border border-white/40 rounded-lg hover:bg-white/10 transition font-medium">
                Bergabung
            </a>
            @endguest
        </div>
    </div>
</section>

{{-- Cabang --}}
<section class="max-w-6xl mx-auto px-4 py-12">
    <p class="sc-eyebrow mb-2">EMPAT CABANG</p>
    <h2 class="text-2xl md:text-3xl font-bold text-sc-ink-900 mb-6">Cabang Kami</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($cabangs as $cabang)
        <a href="{{ route('cabang.show', $cabang->slug) }}"
           class="bg-white border border-sc-line rounded-xl p-5 text-center hover:shadow-sc-3 hover:border-sc-teal-300 transition group">
            <div class="w-10 h-10 mx-auto mb-3 rounded-lg bg-sc-teal-100 text-sc-teal-700 flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <p class="font-semibold text-sc-ink-900 group-hover:text-sc-teal-700 transition text-sm">{{ $cabang->nama }}</p>
        </a>
        @endforeach
    </div>
</section>

{{-- Blog Terbaru --}}
<section class="max-w-6xl mx-auto px-4 pb-16">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="sc-eyebrow mb-1">CERITA TERBARU</p>
            <h2 class="text-2xl md:text-3xl font-bold text-sc-ink-900">Blog Terbaru</h2>
        </div>
        <a href="{{ route('blog.index') }}" class="text-sc-teal-700 hover:text-sc-teal-800 text-sm font-semibold">
            Lihat semua →
        </a>
    </div>
    @if($blogs->isEmpty())
    <p class="text-center text-sc-ink-500 py-12">Belum ada blog. Jadilah yang pertama menulis!</p>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($blogs as $blog)
            @include('blog._card', ['blog' => $blog])
        @endforeach
    </div>
    @endif
</section>
@endsection
