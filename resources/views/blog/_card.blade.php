<article class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
    @if($blog->image)
    <img src="{{ asset('storage/'.$blog->image) }}" alt="{{ $blog->title }}" class="h-48 w-full object-cover">
    @else
    <div class="h-48 bg-gradient-to-br from-[#1e3a5f] to-[#2d5282] flex items-center justify-center">
        <span class="text-white/40 text-4xl">📝</span>
    </div>
    @endif
    <div class="p-5 flex flex-col flex-1">
        <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
            <span class="bg-[#1e3a5f]/10 text-[#1e3a5f] px-2 py-0.5 rounded-full">{{ $blog->cabang?->nama }}</span>
            <span>{{ \Carbon\Carbon::parse($blog->published_at)->translatedFormat('j F Y') }}</span>
        </div>
        <h2 class="font-bold text-gray-800 text-lg leading-snug mb-2 line-clamp-2">
            <a href="{{ route('blog.show', $blog->slug) }}" class="hover:text-[#1e3a5f]">{{ $blog->title }}</a>
        </h2>
        <div class="flex flex-wrap gap-1 mb-3">
            @foreach($blog->tags ?? [] as $tag)
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $tag->name }}</span>
            @endforeach
        </div>
        <div class="mt-auto flex items-center gap-2">
            <img src="{{ $blog->user?->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($blog->user?->name ?? 'U').'&size=32&background=1e3a5f&color=fff' }}"
                 class="w-7 h-7 rounded-full object-cover" alt="{{ $blog->user?->name }}">
            <a href="{{ route('profile.show', $blog->user?->username ?? '') }}"
               class="text-sm text-gray-600 hover:text-[#1e3a5f]">
                {{ $blog->user?->name }}
            </a>
        </div>
    </div>
</article>
