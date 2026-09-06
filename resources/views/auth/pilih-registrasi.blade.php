@extends('layouts.app')

@section('title', 'Daftar - Study Center Nias')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="w-full max-w-xl">
        <h1 class="text-2xl font-bold text-sc-teal-800 mb-2 text-center">Daftar Akun</h1>
        <p class="text-sc-ink-500 text-sm text-center mb-8">
            Pilih jenis pendaftaran yang sesuai denganmu
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Tamu --}}
            <a href="{{ route('register.tamu') }}"
               class="bg-white rounded-2xl shadow-lg p-8 flex flex-col items-center text-center hover:shadow-xl hover:border-sc-teal-500 border-2 border-transparent transition group">
                <div class="w-16 h-16 rounded-full bg-sc-teal-50 flex items-center justify-center mb-4 group-hover:bg-sc-teal-100 transition">
                    <svg class="w-8 h-8 text-sc-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-sc-teal-800 mb-1">Tamu</h2>
                <p class="text-sc-ink-500 text-sm">Bergabung untuk membaca dan berkomentar di blog Study Center Nias</p>
            </a>

            {{-- Siswa --}}
            <a href="{{ route('pendaftaran.pilih-cabang') }}"
               class="bg-white rounded-2xl shadow-lg p-8 flex flex-col items-center text-center hover:shadow-xl hover:border-sc-teal-500 border-2 border-transparent transition group">
                <div class="w-16 h-16 rounded-full bg-sc-teal-50 flex items-center justify-center mb-4 group-hover:bg-sc-teal-100 transition">
                    <svg class="w-8 h-8 text-sc-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-3.5 2M12 20l3.5-8"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-sc-teal-800 mb-1">Siswa</h2>
                <p class="text-sc-ink-500 text-sm">Daftarkan dirimu sebagai calon siswa Study Center Nias</p>
            </a>
        </div>

        <p class="text-center text-sm text-gray-500 mt-8">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-sc-teal-700 font-medium hover:underline">Masuk</a>
        </p>
    </div>
</div>
@endsection
