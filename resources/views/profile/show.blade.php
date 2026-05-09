@extends('layouts.app')

@section('title', $user->name . ' - Study Center Nias')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl shadow-sm p-8 mb-8">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=120&background=1e3a5f&color=fff' }}"
                 class="w-28 h-28 rounded-full object-cover border-4 border-[#c9a84c]" alt="{{ $user->name }}">
            <div class="flex-1 text-center sm:text-left">
                <h1 class="text-2xl font-bold text-[#1e3a5f]">{{ $user->name }}</h1>
                <p class="text-gray-500 text-sm mt-1">
                    <span class="capitalize">{{ $user->role?->name }}</span>
                    @if($user->cabang)
                    · {{ $user->cabang->nama }}
                    @endif
                </p>
                @if($user->bio)
                <p class="text-gray-700 mt-3 max-w-md">{{ $user->bio }}</p>
                @endif

                {{-- Social Links --}}
                @if($user->socialLinks->count())
                <div class="flex gap-3 mt-4 justify-center sm:justify-start">
                    @foreach($user->socialLinks as $link)
                    <a href="{{ $link->platform === 'whatsapp' ? 'https://wa.me/'.preg_replace('/\D/','', $link->value) : ($link->platform === 'email' ? 'mailto:'.$link->value : $link->value) }}"
                       target="_blank"
                       class="text-xs px-3 py-1 bg-[#1e3a5f]/10 text-[#1e3a5f] rounded-full hover:bg-[#1e3a5f] hover:text-white transition capitalize">
                        {{ $link->platform }}
                    </a>
                    @endforeach
                </div>
                @endif

                <div class="flex gap-3 mt-4 justify-center sm:justify-start flex-wrap">
                    @auth
                    @if(auth()->user()->username === $user->username)
                    <a href="{{ route('profile.edit') }}"
                       class="text-sm px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2d5282]">
                        Edit Profil
                    </a>
                    @if($user->cv_enabled)
                    <a href="{{ route('cv.show', $user->username) }}"
                       class="text-sm px-4 py-2 border rounded-lg hover:bg-gray-50">Download CV</a>
                    <a href="{{ route('cv.kartu-nama', $user->username) }}"
                       class="text-sm px-4 py-2 border rounded-lg hover:bg-gray-50">Kartu Nama</a>
                    @endif
                    @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{-- Blog list --}}
    <h2 class="text-xl font-bold text-[#1e3a5f] mb-4">Artikel ({{ $blogs->count() }})</h2>
    @if($blogs->isEmpty())
    <p class="text-gray-400 text-center py-12">Belum ada artikel.</p>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($blogs as $blog)
            @include('blog._card', ['blog' => $blog])
        @endforeach
    </div>
    @endif
</div>
@endsection
