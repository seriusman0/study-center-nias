@extends('layouts.app')

@section('title', $blog->title . ' - Study Center Nias')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex flex-wrap gap-2 mb-3">
            <a href="{{ route('cabang.show', $blog->cabang?->slug) }}"
               class="text-xs bg-[#1e3a5f]/10 text-[#1e3a5f] px-3 py-1 rounded-full">
                {{ $blog->cabang?->nama }}
            </a>
            @foreach($blog->tags as $tag)
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ $tag->name }}</span>
            @endforeach
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $blog->title }}</h1>
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <img src="{{ $blog->user?->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($blog->user?->name ?? 'U').'&size=40&background=1e3a5f&color=fff' }}"
                     class="w-10 h-10 rounded-full object-cover" alt="{{ $blog->user?->name }}">
                <div>
                    <a href="{{ route('profile.show', $blog->user?->username ?? '') }}"
                       class="font-semibold hover:text-[#1e3a5f] text-sm">{{ $blog->user?->name }}</a>
                    <p class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($blog->published_at)->translatedFormat('j F Y') }}
                    </p>
                </div>
            </div>
            @auth
            @if(auth()->id() === $blog->user_id || auth()->user()->isAdmin())
            <div class="flex gap-2">
                <a href="{{ route('blog.edit', $blog->slug) }}"
                   class="text-sm px-3 py-1 border rounded-lg hover:bg-gray-50">Edit</a>
                <form method="POST" action="{{ route('blog.destroy', $blog->id) }}"
                      onsubmit="return confirm('Hapus blog ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-sm px-3 py-1 border border-red-200 text-red-600 rounded-lg hover:bg-red-50">
                        Hapus
                    </button>
                </form>
            </div>
            @endif
            @endauth
        </div>
    </div>

    {{-- Image --}}
    @if($blog->image)
    <img src="{{ asset('storage/'.$blog->image) }}" alt="{{ $blog->title }}"
         class="w-full h-72 object-cover rounded-xl mb-8">
    @endif

    {{-- Content --}}
    <div class="prose prose-gray max-w-none mb-12">
        {!! $blog->content !!}
    </div>

    {{-- Comments --}}
    <section class="border-t pt-8">
        <h2 class="text-xl font-bold text-[#1e3a5f] mb-6">Komentar ({{ $comments->count() }})</h2>

        @auth
        <form method="POST" action="{{ route('comment.store', $blog->id) }}" class="mb-8">
            @csrf
            <textarea name="content" placeholder="Tulis komentar..." rows="3" required
                      class="w-full border rounded-xl px-4 py-3 text-sm outline-[#1e3a5f] resize-none"></textarea>
            <button type="submit"
                    class="mt-2 px-5 py-2 bg-[#1e3a5f] text-white rounded-lg text-sm hover:bg-[#2d5282]">
                Kirim Komentar
            </button>
        </form>
        @else
        <p class="text-sm text-gray-500 mb-6">
            <a href="{{ route('login') }}" class="text-[#1e3a5f] font-medium hover:underline">Masuk</a>
            untuk berkomentar.
        </p>
        @endauth

        <div class="space-y-4">
            @forelse($comments as $comment)
                @include('blog._comment', ['comment' => $comment])
            @empty
            <p class="text-gray-400 text-sm text-center py-8">Belum ada komentar. Jadilah yang pertama!</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
