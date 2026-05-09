<div class="border-l-2 border-gray-100 pl-4 py-2">
    <div class="flex items-center gap-2 mb-1">
        <img src="{{ $comment->user?->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($comment->user?->name ?? 'U').'&size=28&background=1e3a5f&color=fff' }}"
             class="w-7 h-7 rounded-full object-cover" alt="">
        <a href="{{ route('profile.show', $comment->user?->username ?? '') }}"
           class="text-sm font-semibold hover:text-[#1e3a5f]">{{ $comment->user?->name }}</a>
        <span class="text-xs text-gray-400">
            {{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
        </span>
        @auth
        @if(auth()->id() === $comment->user_id || auth()->user()->isAdmin())
        <form method="POST" action="{{ route('comment.destroy', $comment->id) }}" class="ml-auto">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-red-400 hover:text-red-600">Hapus</button>
        </form>
        @endif
        @endauth
    </div>
    <p class="text-gray-700 text-sm ml-9">{{ $comment->content }}</p>

    @foreach($comment->replies ?? [] as $reply)
    <div class="ml-6 mt-2">
        @include('blog._comment', ['comment' => $reply])
    </div>
    @endforeach
</div>
