@extends('layouts.app')

@section('title', $cabang->nama . ' - Study Center Nias')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="mb-8">
        <a href="{{ route('cabang.index') }}" class="text-sm text-sc-ink-500 hover:text-sc-teal-700">← Cabang</a>
        <h1 class="text-3xl font-bold text-sc-ink-900 mt-2">{{ $cabang->nama }}</h1>
        @if($cabang->alamat)
        <p class="text-sc-ink-500 mt-1">📍 {{ $cabang->alamat }}</p>
        @endif
        @if($cabang->kontak)
        <p class="text-sc-ink-500">📞 {{ $cabang->kontak }}</p>
        @endif
    </div>

    @if($blogs->isEmpty())
    <p class="text-center text-sc-ink-500 py-16">Belum ada blog dari cabang ini.</p>
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
                  {{ $i == $blogs->currentPage() ? 'bg-sc-teal-700 text-white' : 'bg-white text-sc-ink-700 hover:bg-sc-teal-50 border border-sc-line' }}">
            {{ $i }}
        </a>
        @endfor
    </div>
    @endif
    @endif
</div>
@endsection
