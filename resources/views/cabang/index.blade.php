@extends('layouts.app')

@section('title', 'Cabang - Study Center Nias')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <p class="sc-eyebrow mb-1">EMPAT WILAYAH</p>
    <h1 class="text-3xl font-bold text-sc-ink-900 mb-2">Cabang</h1>
    <p class="text-sc-ink-500 mb-8">Study Center hadir di 4 wilayah Nias</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($cabangs as $cabang)
        <a href="{{ route('cabang.show', $cabang->slug) }}"
           class="bg-white border border-sc-line rounded-xl p-6 hover:shadow-sc-3 hover:border-sc-teal-300 transition group">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-sc-teal-100 text-sc-teal-700 flex items-center justify-center flex-shrink-0">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div class="flex-1">
                    <h2 class="font-bold text-lg text-sc-ink-900 group-hover:text-sc-teal-700 transition">
                        {{ $cabang->nama }}
                    </h2>
                    @if($cabang->alamat)
                    <p class="text-sm text-sc-ink-500 mt-1">{{ $cabang->alamat }}</p>
                    @endif
                    @if($cabang->kontak)
                    <p class="text-sm text-sc-ink-500">📞 {{ $cabang->kontak }}</p>
                    @endif
                    <p class="text-xs text-sc-ink-500 mt-2 font-semibold">{{ $cabang->blogs_count }} artikel</p>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
