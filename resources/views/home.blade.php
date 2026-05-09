@extends('layouts.app')

@section('title', 'Study Center Nias')

@section('content')
{{-- Hero --}}
<section class="bg-gradient-to-br from-[#1e3a5f] to-[#2d5282] text-white py-20 px-4">
    <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">
            Study Center <span class="text-[#c9a84c]">Nias</span>
        </h1>
        <p class="text-white/80 text-lg mb-8 max-w-2xl mx-auto">
            Platform komunitas digital untuk Study Center di 4 cabang wilayah Nias.
            Berbagi pengetahuan, bertumbuh bersama.
        </p>
        <div class="flex gap-3 justify-center flex-wrap">
            <a href="{{ route('blog.index') }}"
               class="px-6 py-3 bg-[#c9a84c] text-[#1e3a5f] font-semibold rounded-lg hover:bg-yellow-400 transition">
                Baca Blog
            </a>
            @guest
            <a href="{{ route('register') }}"
               class="px-6 py-3 border border-white/50 rounded-lg hover:bg-white/10 transition">
                Bergabung
            </a>
            @endguest
        </div>
    </div>
</section>

{{-- Cabang --}}
<section class="max-w-6xl mx-auto px-4 py-12">
    <h2 class="text-2xl font-bold text-[#1e3a5f] mb-6">Cabang Kami</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($cabangs as $cabang)
        <a href="{{ route('cabang.show', $cabang->slug) }}"
           class="bg-white border rounded-xl p-5 text-center hover:shadow-md hover:border-[#1e3a5f] transition group">
            <div class="text-3xl mb-2">🏫</div>
            <p class="font-semibold text-[#1e3a5f] group-hover:text-[#c9a84c] transition text-sm">{{ $cabang->nama }}</p>
        </a>
        @endforeach
    </div>
</section>

{{-- Blog Terbaru --}}
<section class="max-w-6xl mx-auto px-4 pb-16">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-[#1e3a5f]">Blog Terbaru</h2>
        <a href="{{ route('blog.index') }}" class="text-[#1e3a5f] hover:text-[#c9a84c] text-sm font-medium">
            Lihat semua →
        </a>
    </div>
    @if($blogs->isEmpty())
    <p class="text-center text-gray-500 py-12">Belum ada blog. Jadilah yang pertama menulis!</p>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($blogs as $blog)
            @include('blog._card', ['blog' => $blog])
        @endforeach
    </div>
    @endif
</section>
@endsection
