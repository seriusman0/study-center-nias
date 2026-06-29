@extends('layouts.app')

@section('title', $user->name . ' - Study Center Nias')

@php
    $roleName = $user->roles->first()?->name ?? '';
    $ringClass = match($roleName) {
        'admin' => 'ring-sc-teal-700',
        'fulltimer' => 'ring-sc-yellow-600',
        'mentor' => 'ring-sc-orange-500',
        default => 'ring-sc-orange-500',
    };
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl shadow-sc-2 border border-sc-line p-8 mb-8">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=120&background=007a5c&color=fff' }}"
                 class="w-28 h-28 rounded-full object-cover ring-4 {{ $ringClass }}" alt="{{ $user->name }}">
            <div class="flex-1 text-center sm:text-left">
                <h1 class="text-2xl font-bold text-sc-ink-900">{{ $user->name }}</h1>
                <p class="text-sc-ink-500 text-sm mt-1">
                    <span class="capitalize font-semibold text-sc-teal-700">{{ $user->roles->pluck('name')->implode(', ') }}</span>
                    @if($user->cabang)
                    · {{ $user->cabang->nama }}
                    @endif
                </p>
                @if($user->bio)
                <p class="text-sc-ink-700 mt-3 max-w-md leading-relaxed">{{ $user->bio }}</p>
                @endif

                {{-- Social Links --}}
                @if($user->socialLinks->count())
                <div class="flex gap-3 mt-4 justify-center sm:justify-start flex-wrap">
                    @foreach($user->socialLinks as $link)
                    <a href="{{ $link->platform === 'whatsapp' ? 'https://wa.me/'.preg_replace('/\D/','', $link->value) : ($link->platform === 'email' ? 'mailto:'.$link->value : $link->value) }}"
                       target="_blank"
                       class="text-xs px-3 py-1 bg-sc-teal-100 text-sc-teal-800 rounded-full hover:bg-sc-teal-700 hover:text-white transition capitalize font-semibold">
                        {{ $link->platform }}
                    </a>
                    @endforeach
                </div>
                @endif

                <div class="flex gap-3 mt-4 justify-center sm:justify-start flex-wrap">
                    @auth
                    @if(auth()->user()->username === $user->username)
                    <a href="{{ route('profile.edit') }}"
                       class="text-sm px-4 py-2 bg-sc-teal-600 hover:bg-sc-teal-700 text-white rounded-lg font-semibold transition">
                        Edit Profil
                    </a>
                    @if($user->cv_enabled)
                    <a href="{{ route('cv.show', $user->username) }}"
                       class="text-sm px-4 py-2 border border-sc-line text-sc-ink-700 rounded-lg hover:bg-sc-line-soft transition font-semibold">Download CV</a>
                    <a href="{{ route('cv.kartu-nama', $user->username) }}"
                       class="text-sm px-4 py-2 border border-sc-line text-sc-ink-700 rounded-lg hover:bg-sc-line-soft transition font-semibold">Kartu Nama</a>
                    @endif
                    @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{-- Blog list --}}
    <h2 class="text-xl font-bold text-sc-ink-900 mb-4">Artikel ({{ $blogs->count() }})</h2>
    @if($blogs->isEmpty())
    <p class="text-sc-ink-500 text-center py-12">Belum ada artikel.</p>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($blogs as $blog)
            @include('blog._card', ['blog' => $blog])
        @endforeach
    </div>
    @endif
</div>
@endsection
