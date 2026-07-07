@extends('layouts.app')

@section('title', 'Cek Status Pendaftaran — ' . $user->name)

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">

        <div class="bg-white rounded-2xl shadow-sm border border-sc-line p-8">

            <div class="text-center mb-6">
                <h1 class="text-xl font-bold text-gray-800">Status Pendaftaran</h1>
                <p class="text-2xl font-bold text-sc-teal-700 mt-1">{{ $user->name }}</p>
            </div>

            @php
                $profile = $user->studentProfile;
                $status  = $profile->status ?? 'pending';
            @endphp

            {{-- Status Badge --}}
            <div class="text-center mb-6">
                @if($status === 'diterima')
                    <div class="inline-flex items-center gap-2 bg-green-100 text-green-800 border border-green-300 px-5 py-2 rounded-full font-bold text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Diterima
                    </div>
                @elseif($status === 'ditolak')
                    <div class="inline-flex items-center gap-2 bg-red-100 text-red-800 border border-red-300 px-5 py-2 rounded-full font-bold text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Ditolak
                    </div>
                @elseif($status === 'perbaikan')
                    <div class="inline-flex items-center gap-2 bg-orange-100 text-orange-800 border border-orange-300 px-5 py-2 rounded-full font-bold text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Perlu Perbaikan Data
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 bg-yellow-100 text-yellow-800 border border-yellow-300 px-5 py-2 rounded-full font-bold text-base">
                        <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
                        </svg>
                        Menunggu Validasi
                    </div>
                @endif
            </div>

            {{-- Pesan berdasarkan status --}}
            <div class="rounded-xl p-4 text-sm mb-4
                @if($status === 'diterima') bg-green-50 border border-green-200 text-green-800
                @elseif($status === 'ditolak') bg-red-50 border border-red-200 text-red-800
                @elseif($status === 'perbaikan') bg-orange-50 border border-orange-200 text-orange-800
                @else bg-yellow-50 border border-yellow-200 text-yellow-800
                @endif">
                @if($status === 'diterima')
                    <p class="font-semibold mb-1">Selamat! Pendaftaran Anda telah diterima.</p>
                    <p>Akun Anda sudah aktif. Silakan masuk menggunakan username <strong>{{ '@' . $user->username }}</strong> dan password default Anda.</p>
                @elseif($status === 'ditolak')
                    <p class="font-semibold mb-1">Maaf, pendaftaran Anda tidak dapat diproses.</p>
                    <p>Hubungi pengurus untuk informasi lebih lanjut.</p>
                @elseif($status === 'perbaikan')
                    <p class="font-semibold mb-1">Data Anda memerlukan perbaikan.</p>
                    <p>Silakan hubungi pengurus dengan membawa data yang benar untuk diperbarui.</p>
                @else
                    <p class="font-semibold mb-1">Pendaftaran Anda sedang dalam proses validasi.</p>
                    <p>Pengurus akan segera memeriksa data Anda. Mohon bersabar.</p>
                @endif
            </div>

            {{-- Catatan admin --}}
            @if($profile->catatan_admin)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm mb-4">
                <p class="font-semibold text-gray-700 mb-1">Catatan dari Pengurus:</p>
                <p class="text-gray-600 italic">{{ $profile->catatan_admin }}</p>
            </div>
            @endif

            {{-- Info login jika diterima --}}
            @if($status === 'diterima')
            <div class="bg-sc-teal-50 border border-sc-teal-200 rounded-xl p-4 text-sm mb-4">
                <p class="font-semibold text-sc-teal-800 mb-2">Informasi Login</p>
                <div class="space-y-1 text-sc-teal-700">
                    <div class="flex gap-2">
                        <span class="w-24 text-sc-teal-500">Username</span>
                        <span class="font-mono font-bold">{{ $user->username }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-24 text-sc-teal-500">Password</span>
                        <span class="font-mono font-bold">12345 <span class="font-sans font-normal text-xs">(segera ganti)</span></span>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('login') }}" class="block w-full text-center bg-sc-teal-600 text-white text-sm font-semibold px-4 py-3 rounded-xl hover:bg-sc-teal-700 transition">
                        Masuk Sekarang →
                    </a>
                </div>
            </div>
            @endif

            <a href="{{ route('home') }}" class="block text-center text-sm text-gray-400 hover:text-sc-teal-600 hover:underline mt-2">
                ← Kembali ke Beranda
            </a>

        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            Simpan halaman ini untuk memantau status pendaftaran Anda.
        </p>

    </div>
</div>
@endsection
