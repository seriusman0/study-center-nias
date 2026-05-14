@extends('layouts.app')

@section('title', $blog->title . ' - Study Center Nias')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex flex-wrap gap-2 mb-3">
            <a href="{{ route('cabang.show', $blog->cabang?->slug) }}"
               class="text-xs bg-sc-teal-100 text-sc-teal-800 font-semibold px-3 py-1 rounded-full">
                {{ $blog->cabang?->nama }}
            </a>
            @foreach($blog->tags as $tag)
            <span class="text-xs bg-sc-line-soft text-sc-ink-700 px-2 py-1 rounded-full">{{ $tag->name }}</span>
            @endforeach
        </div>
        <h1 class="font-display text-3xl md:text-4xl text-sc-ink-900 mb-4 leading-tight">{{ $blog->title }}</h1>
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <img src="{{ $blog->user?->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($blog->user?->name ?? 'U').'&size=40&background=007a5c&color=fff' }}"
                     class="w-10 h-10 rounded-full object-cover ring-2 ring-sc-orange-500" alt="{{ $blog->user?->name }}">
                <div>
                    <a href="{{ route('profile.show', $blog->user?->username ?? '') }}"
                       class="font-semibold hover:text-sc-teal-700 text-sm">{{ $blog->user?->name }}</a>
                    <p class="text-xs text-sc-ink-500">
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
    <div class="prose prose-gray max-w-none mb-12 blog-content">
        {!! $blog->content !!}
    </div>
    <style>
        .blog-content img { max-width: 100%; height: auto; border-radius: 0.75rem; margin: 1rem auto; display: block; }
        .blog-content h2 { font-size: 1.5rem; font-weight: 700; color: var(--sc-teal-800); margin: 1.5rem 0 0.75rem; }
        .blog-content h3 { font-size: 1.25rem; font-weight: 600; color: var(--sc-teal-800); margin: 1.25rem 0 0.5rem; }
        .blog-content blockquote { border-left: 4px solid var(--sc-orange-500); padding: 0.5rem 1rem; color: var(--sc-ink-700); background: var(--sc-bg-alt); margin: 1rem 0; font-family: var(--font-display); font-size: 1.1rem; }
        .blog-content pre { background: var(--sc-ink-900); color: #f9fafb; padding: 0.75rem 1rem; border-radius: 0.5rem; overflow-x: auto; }
        .blog-content ul { list-style: disc; padding-left: 1.5rem; }
        .blog-content ol { list-style: decimal; padding-left: 1.5rem; }
        .blog-content a { color: var(--sc-teal-700); text-decoration: underline; }
        .blog-content iframe { max-width: 100%; border-radius: 0.75rem; }
    </style>

    {{-- Comments --}}
    <section class="border-t border-sc-line pt-8">
        <h2 class="text-xl font-bold text-sc-ink-900 mb-6">Komentar ({{ $comments->count() }})</h2>

        @auth
        <form method="POST" action="{{ route('comment.store', $blog->id) }}" class="mb-8">
            @csrf
            <textarea name="content" placeholder="Tulis komentar..." rows="3" required
                      class="w-full border border-sc-line rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20 resize-none"></textarea>
            <button type="submit"
                    class="mt-2 px-5 py-2 bg-sc-teal-600 text-white rounded-lg text-sm font-semibold hover:bg-sc-teal-700 transition">
                Kirim Komentar
            </button>
        </form>
        @else
        <p class="text-sm text-sc-ink-500 mb-6">
            <a href="{{ route('login') }}" class="text-sc-teal-700 font-medium hover:underline">Masuk</a>
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
