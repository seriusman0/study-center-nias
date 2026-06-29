<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Study Center Nias')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans bg-sc-bg text-sc-ink-900 min-h-screen flex flex-col">
    {{-- Toast notifications --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-4 right-4 z-50 bg-sc-teal-600 text-white px-5 py-3 rounded-xl shadow-sc-3 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
         class="fixed top-4 right-4 z-50 bg-red-600 text-white px-5 py-3 rounded-xl shadow-sc-3 text-sm font-medium max-w-sm">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    {{-- Navbar --}}
    <nav class="bg-sc-teal-700 text-white shadow-sc-2 sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-lg">
                <span class="text-sc-yellow-300">Study Center</span>
                <span class="text-white/80 text-sm font-normal hidden sm:block">Nias</span>
            </a>
            <div class="flex items-center gap-1 text-sm">
                <a href="{{ route('blog.index') }}"
                   class="px-3 py-2 rounded hover:bg-white/10 {{ request()->routeIs('blog.*') ? 'text-sc-yellow-300' : '' }}">
                    Blog
                </a>
                <a href="{{ route('cabang.index') }}"
                   class="px-3 py-2 rounded hover:bg-white/10 {{ request()->routeIs('cabang.*') ? 'text-sc-yellow-300' : '' }}">
                    Cabang
                </a>
                @auth
                    @if(auth()->user()->hasRole('student'))
                        <a href="{{ route('jurnal.index') }}"
                           class="px-3 py-2 rounded hover:bg-white/10 {{ request()->routeIs('jurnal.*') ? 'text-sc-yellow-300' : '' }}">Jurnal</a>
                    @endif
                    @if(auth()->user()->hasRole('mentor'))
                        <a href="{{ route('mentor-presensi.index') }}"
                           class="px-3 py-2 rounded hover:bg-white/10 {{ request()->routeIs('mentor-presensi.*') ? 'text-sc-yellow-300' : '' }}">Presensi Saya</a>
                    @endif
                    @if(auth()->user()->hasRole(['admin','fulltimer','mentor','student']))
                        <a href="{{ route('blog.create') }}" class="px-3 py-2 rounded hover:bg-white/10">Tulis</a>
                    @endif
                    @if(auth()->user()->hasRole(['admin','mentor']))
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded hover:bg-white/10">
                            {{ auth()->user()->isAdmin() ? 'Admin' : 'Panel Mentor' }}
                        </a>
                    @endif
                    <a href="{{ route('profile.show', auth()->user()->username) }}" class="flex items-center gap-2 ml-2">
                        <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=007a5c&color=fff' }}"
                             class="w-8 h-8 rounded-full border-2 border-sc-orange-500 object-cover" alt="{{ auth()->user()->name }}">
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded hover:bg-white/10 text-white/70">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded hover:bg-white/10">Masuk</a>
                    <a href="{{ route('register') }}"
                       class="ml-1 px-4 py-2 bg-sc-orange-500 text-white rounded-lg font-semibold hover:bg-sc-orange-600 transition-colors">
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
    <footer class="bg-sc-teal-700 text-white/70 text-sm py-8 mt-auto">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="text-sc-yellow-300 font-semibold mb-1">Study Center Nias</p>
            <p>Gunungsitoli · Kab. Nias · Kab. Nias Selatan · Kab. Nias Utara</p>
            <p class="mt-3 text-white/40">© {{ date('Y') }} Study Center Nias</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
