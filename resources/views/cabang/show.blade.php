@extends('layouts.app')

@section('title', $cabang->nama . ' - Study Center Nias')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="mb-8">
        <a href="{{ route('cabang.index') }}" class="text-sm text-gray-400 hover:text-[#1e3a5f]">← Cabang</a>
        <h1 class="text-3xl font-bold text-[#1e3a5f] mt-2">{{ $cabang->nama }}</h1>
        @if($cabang->alamat)
        <p class="text-gray-500 mt-1">📍 {{ $cabang->alamat }}</p>
        @endif
        @if($cabang->kontak)
        <p class="text-gray-500">📞 {{ $cabang->kontak }}</p>
        @endif
    </div>

    @if($blogs->isEmpty())
    <p class="text-center text-gray-400 py-16">Belum ada blog dari cabang ini.</p>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($blogs as $blog)
            @include('blog._card', ['blog' => $blog])
        @endforeach
    </div>
    @if($blogs->lastPage() > 1)
    <div class="flex justify-center gap-2 mt-8">
        @for($i = 1; $i <= $blogs->lastPage(); $i++)
        <a href="{{ $blogs->url($i) }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg text-sm font-medium transition
                  {{ $i == $blogs->currentPage() ? 'bg-[#1e3a5f] text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border' }}">
            {{ $i }}
        </a>
        @endfor
    </div>
    @endif
    @endif
</div>
@endsection
