@extends('layouts.app')

@section('title', 'Cabang - Study Center Nias')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold text-[#1e3a5f] mb-2">Cabang</h1>
    <p class="text-gray-500 mb-8">Study Center hadir di 4 wilayah Nias</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($cabangs as $cabang)
        <a href="{{ route('cabang.show', $cabang->slug) }}"
           class="bg-white border rounded-xl p-6 hover:shadow-md hover:border-[#1e3a5f] transition group">
            <div class="flex items-start gap-4">
                <div class="text-4xl">🏫</div>
                <div>
                    <h2 class="font-bold text-lg text-[#1e3a5f] group-hover:text-[#c9a84c] transition">
                        {{ $cabang->nama }}
                    </h2>
                    @if($cabang->alamat)
                    <p class="text-sm text-gray-500 mt-1">{{ $cabang->alamat }}</p>
                    @endif
                    @if($cabang->kontak)
                    <p class="text-sm text-gray-400">📞 {{ $cabang->kontak }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-2">{{ $cabang->blogs_count }} artikel</p>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
