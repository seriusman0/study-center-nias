<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Study Center Nias')</title>

    {{-- PWA --}}
    <meta name="theme-color" content="#007a5c">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SC Nias">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans bg-sc-bg text-sc-ink-900 min-h-screen flex flex-col">

    {{-- Toast --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-4 right-4 z-50 bg-sc-teal-600 text-white px-5 py-3 rounded-xl shadow-sc-3 text-sm font-medium max-w-xs">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
         class="fixed top-4 right-4 z-50 bg-red-600 text-white px-5 py-3 rounded-xl shadow-sc-3 text-sm font-medium max-w-xs">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    @php
        $isCollegeUser = auth()->check() && auth()->user()->hasRole('college');
        $hasBottomNav  = auth()->check() && auth()->user()->hasRole(['student','college','scholarship_teenager','mentor']);
        $isAdminMentor = auth()->check() && auth()->user()->hasRole(['admin','mentor']);
    @endphp

    {{-- Top Navbar --}}
    <nav class="bg-sc-teal-700 text-white shadow-sc-2 sticky top-0 z-40"
         style="padding-top: env(safe-area-inset-top)">
        <div class="max-w-3xl mx-auto px-4 flex items-center justify-between h-14">
            {{-- Logo --}}
            <a href="{{ auth()->check() && auth()->user()->hasRole(['student','college','scholarship_teenager']) ? route('beranda') : route('home') }}"
               class="flex items-center gap-2 font-bold text-base">
                <span class="text-sc-yellow-300">{{ $isCollegeUser ? 'Mahasiswa' : 'SC' }}</span>
                <span class="text-white/80 text-sm font-normal">Nias</span>
            </a>

            {{-- Right side --}}
            <div class="flex items-center gap-1">
                @auth
                    {{-- Admin/Mentor: show navigation links (they don't get bottom nav) --}}
                    @if($isAdminMentor && !$hasBottomNav)
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 text-sm rounded hover:bg-white/10">Panel</a>
                    @endif
                    {{-- Admin/mentor who also have student roles: show admin link --}}
                    @if($hasBottomNav && auth()->user()->hasRole(['admin','mentor']))
                        <a href="{{ route('admin.dashboard') }}"
                           class="text-xs px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 font-semibold">
                            Admin
                        </a>
                    @endif
                    {{-- Non-bottom-nav authenticated users: full nav --}}
                    @if(!$hasBottomNav && !$isAdminMentor)
                        <a href="{{ route('profile.show', auth()->user()->username) }}" class="flex items-center gap-2">
                            <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=007a5c&color=fff' }}"
                                 class="w-7 h-7 rounded-full border-2 border-sc-orange-500 object-cover" alt="">
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-2 text-sm rounded hover:bg-white/10 text-white/70">Keluar</button>
                        </form>
                    @endif
                    {{-- bottom-nav users: show avatar only (profile in bottom nav) --}}
                    @if($hasBottomNav)
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg text-white/60 hover:bg-white/15">
                                Keluar
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-sm rounded hover:bg-white/10">Masuk</a>
                    <a href="{{ route('register') }}"
                       class="ml-1 px-4 py-2 bg-sc-orange-500 text-white rounded-lg text-sm font-semibold hover:bg-sc-orange-600 transition-colors">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Announcements --}}
    @include('partials.announcement-banner')

    {{-- Main Content --}}
    <main class="flex-1" style="{{ $hasBottomNav ? 'padding-bottom: calc(4rem + env(safe-area-inset-bottom))' : '' }}">
        @yield('content')
    </main>

    {{-- Footer — hanya untuk guest --}}
    @guest
    <footer class="bg-sc-teal-700 text-white/70 text-sm py-8 mt-auto">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <p class="text-sc-yellow-300 font-semibold mb-1">Study Center Nias</p>
            <p class="text-xs">Gunungsitoli · Kab. Nias · Kab. Nias Selatan · Kab. Nias Utara</p>
            <p class="mt-4">
                <a href="{{ route('download.android') }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">
                    Download Aplikasi Android
                </a>
            </p>
            <p class="mt-3 text-white/40 text-xs">© {{ date('Y') }} Study Center Nias</p>
        </div>
    </footer>
    @endguest

    {{-- Bottom Tab Bar --}}
    @if($hasBottomNav)
    @php
        $u = auth()->user();

        // Beranda
        $berandaUrl    = $u->hasRole(['student','college','scholarship_teenager']) ? route('beranda') : route('home');
        $berandaActive = request()->routeIs('beranda') || request()->routeIs('home');

        // Jurnal
        if ($u->hasRole('college')) {
            $jurnalUrl    = route('college-jurnal.index');
            $jurnalActive = request()->routeIs('college-jurnal.*');
        } elseif ($u->hasRole('scholarship_teenager')) {
            $jurnalUrl    = route('scholarship-teenager-jurnal.index');
            $jurnalActive = request()->routeIs('scholarship-teenager-jurnal.*');
        } elseif ($u->hasRole('student')) {
            $jurnalUrl    = route('jurnal.index');
            $jurnalActive = request()->routeIs('jurnal.*');
        } else {
            $jurnalUrl    = route('mentor-presensi.index');
            $jurnalActive = request()->routeIs('mentor-presensi.*');
        }

        // Laporan
        if ($u->hasRole(['student','college','scholarship_teenager'])) {
            $laporanUrl    = route('laporan');
            $laporanActive = request()->routeIs('laporan');
        } else {
            $laporanUrl    = route('admin.jurnal.reports.index');
            $laporanActive = request()->routeIs('admin.jurnal.reports.*');
        }

        // Profil
        $profilUrl    = route('profile.show', $u->username);
        $profilActive = request()->routeIs('profile.*');
    @endphp
    <nav id="bottom-nav"
         style="position:fixed;bottom:0;left:0;right:0;z-index:50;background:#fff;border-top:1px solid #e5e7eb;display:flex;padding-bottom:env(safe-area-inset-bottom)">
        {{-- Beranda --}}
        <a href="{{ $berandaUrl }}" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:.6rem 0;gap:.2rem;text-decoration:none;color:{{ $berandaActive ? '#007a5c' : '#9ca3af' }}">
            <svg width="22" height="22" fill="{{ $berandaActive ? '#007a5c' : 'none' }}" stroke="{{ $berandaActive ? '#007a5c' : '#9ca3af' }}" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span style="font-size:.65rem;font-weight:{{ $berandaActive ? '700' : '500' }}">Beranda</span>
        </a>

        {{-- Jurnal --}}
        <a href="{{ $jurnalUrl }}" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:.6rem 0;gap:.2rem;text-decoration:none;color:{{ $jurnalActive ? '#007a5c' : '#9ca3af' }}">
            <svg width="22" height="22" fill="{{ $jurnalActive ? '#007a5c' : 'none' }}" stroke="{{ $jurnalActive ? '#007a5c' : '#9ca3af' }}" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span style="font-size:.65rem;font-weight:{{ $jurnalActive ? '700' : '500' }}">Jurnal</span>
        </a>

        {{-- Laporan --}}
        <a href="{{ $laporanUrl }}" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:.6rem 0;gap:.2rem;text-decoration:none;color:{{ $laporanActive ? '#007a5c' : '#9ca3af' }}">
            <svg width="22" height="22" fill="{{ $laporanActive ? '#007a5c' : 'none' }}" stroke="{{ $laporanActive ? '#007a5c' : '#9ca3af' }}" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span style="font-size:.65rem;font-weight:{{ $laporanActive ? '700' : '500' }}">Laporan</span>
        </a>

        {{-- Profil --}}
        <a href="{{ $profilUrl }}" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:.6rem 0;gap:.2rem;text-decoration:none;color:{{ $profilActive ? '#007a5c' : '#9ca3af' }}">
            <svg width="22" height="22" fill="{{ $profilActive ? '#007a5c' : 'none' }}" stroke="{{ $profilActive ? '#007a5c' : '#9ca3af' }}" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span style="font-size:.65rem;font-weight:{{ $profilActive ? '700' : '500' }}">Profil</span>
        </a>
    </nav>
    @endif

    {{-- PWA Service Worker --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
