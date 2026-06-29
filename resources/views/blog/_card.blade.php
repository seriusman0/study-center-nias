<article class="bg-white rounded-xl shadow-sc-1 border border-sc-line hover:shadow-sc-3 hover:border-sc-teal-300 transition overflow-hidden flex flex-col">
    @if($blog->image)
    <img src="{{ asset('storage/'.$blog->image) }}" alt="{{ $blog->title }}" class="h-48 w-full object-cover">
    @else
    <div class="h-48 bg-gradient-to-br from-sc-teal-700 to-sc-teal-500 flex items-center justify-center">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6Z"/></svg>
    </div>
    @endif
    <div class="p-5 flex flex-col flex-1">
        <div class="flex items-center gap-2 text-xs text-sc-ink-500 mb-2">
            <span class="bg-sc-teal-100 text-sc-teal-800 px-2 py-0.5 rounded-full font-semibold">{{ $blog->cabang?->nama }}</span>
            <span>{{ \Carbon\Carbon::parse($blog->published_at)->translatedFormat('j F Y') }}</span>
        </div>
        <h2 class="font-bold text-sc-ink-900 text-lg leading-snug mb-2 line-clamp-2">
            <a href="{{ route('blog.show', $blog->slug) }}" class="hover:text-sc-teal-700 transition">{{ $blog->title }}</a>
        </h2>
        <div class="flex flex-wrap gap-1 mb-3">
            @foreach($blog->tags ?? [] as $tag)
            <span class="text-xs bg-sc-line-soft text-sc-ink-700 px-2 py-0.5 rounded">{{ $tag->name }}</span>
            @endforeach
        </div>
        <div class="mt-auto flex items-center gap-2">
            <img src="{{ $blog->user?->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($blog->user?->name ?? 'U').'&size=32&background=007a5c&color=fff' }}"
                 class="w-7 h-7 rounded-full object-cover" alt="{{ $blog->user?->name }}">
            <a href="{{ route('profile.show', $blog->user?->username ?? '') }}"
               class="text-sm text-sc-ink-700 hover:text-sc-teal-700 transition">
                {{ $blog->user?->name }}
            </a>
        </div>
    </div>
</article>
