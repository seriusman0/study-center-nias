<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Study Center Nias')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
    {{-- Toast notifications --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-4 right-4 z-50 bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
         class="fixed top-4 right-4 z-50 bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium max-w-sm">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    {{-- Navbar --}}
    <nav class="bg-[#1e3a5f] text-white shadow-lg sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-lg">
                <span class="text-[#c9a84c]">Study Center</span>
                <span class="text-white/80 text-sm font-normal hidden sm:block">Nias</span>
            </a>
            <div class="flex items-center gap-1 text-sm">
                <a href="{{ route('blog.index') }}"
                   class="px-3 py-2 rounded hover:bg-white/10 {{ request()->routeIs('blog.*') ? 'text-[#c9a84c]' : '' }}">
                    Blog
                </a>
                <a href="{{ route('cabang.index') }}"
                   class="px-3 py-2 rounded hover:bg-white/10 {{ request()->routeIs('cabang.*') ? 'text-[#c9a84c]' : '' }}">
                    Cabang
                </a>
                @auth
                    @if(auth()->user()->hasRole(['admin','fulltimer','mentor','student']))
                        <a href="{{ route('blog.create') }}" class="px-3 py-2 rounded hover:bg-white/10">Tulis</a>
                    @endif
                    @if(auth()->user()->hasRole(['admin','mentor']))
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded hover:bg-white/10">
                            {{ auth()->user()->isAdmin() ? 'Admin' : 'Panel Mentor' }}
                        </a>
                    @endif
                    <a href="{{ route('profile.show', auth()->user()->username) }}" class="flex items-center gap-2 ml-2">
                        <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=1e3a5f&color=fff' }}"
                             class="w-8 h-8 rounded-full border-2 border-[#c9a84c] object-cover" alt="{{ auth()->user()->name }}">
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded hover:bg-white/10 text-white/70">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded hover:bg-white/10">Masuk</a>
                    <a href="{{ route('register') }}"
                       class="ml-1 px-4 py-2 bg-[#c9a84c] text-[#1e3a5f] rounded font-semibold hover:bg-yellow-400">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-1 min-h-[calc(100vh-64px-120px)]">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-[#1e3a5f] text-white/70 text-sm py-8 mt-auto">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="text-[#c9a84c] font-semibold mb-1">Study Center Nias</p>
            <p>Gunungsitoli · Kab. Nias · Kab. Nias Selatan · Kab. Nias Utara</p>
            <p class="mt-3 text-white/40">© {{ date('Y') }} Study Center Nias</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
