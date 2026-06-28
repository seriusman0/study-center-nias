@extends('layouts.app')

@section('title', 'Blog - Study Center Nias')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <p class="sc-eyebrow mb-1">EMPAT CABANG</p>
    <h1 class="text-3xl font-bold text-sc-ink-900 mb-8">Blog</h1>

    {{-- Filters --}}
    <form method="GET" action="{{ route('blog.index') }}"
          class="flex flex-wrap gap-3 mb-8 bg-white p-4 rounded-xl shadow-sc-1 border border-sc-line">
        <input type="search" name="search" placeholder="Cari blog..."
               value="{{ request('search') }}"
               class="border border-sc-line rounded-lg px-3 py-2 text-sm flex-1 min-w-48 focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">
        <select name="cabang" class="border border-sc-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-600">
            <option value="">Semua Cabang</option>
            @foreach($cabangs as $c)
            <option value="{{ $c->slug }}" {{ request('cabang') == $c->slug ? 'selected' : '' }}>{{ $c->nama }}</option>
            @endforeach
        </select>
        <select name="sort" class="border border-sc-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-600">
            <option value="latest" {{ request('sort','latest') == 'latest' ? 'selected' : '' }}>Terbaru</option>
            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Terpopuler</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-sc-teal-600 text-white rounded-lg text-sm font-semibold hover:bg-sc-teal-700 transition">
            Filter
        </button>
    </form>

    {{-- Grid --}}
    @if($blogs->isEmpty())
    <div class="text-center py-20 text-sc-ink-500">
        <p class="text-4xl mb-3">📭</p>
        <p>Tidak ada blog ditemukan.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($blogs as $blog)
            @include('blog._card', ['blog' => $blog])
        @endforeach
    </div>

    {{-- Pagination --}}
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
