@extends('layouts.app')

@section('title', 'Pendaftaran Siswa - Study Center Nias')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-sc-teal-800">Pendaftaran Siswa Baru</h1>
            <p class="text-sc-ink-500 mt-2">Pilih cabang Study Center terdekat untuk melanjutkan pendaftaran.</p>
        </div>

        <div class="grid gap-4">
            @foreach($cabangs as $cabang)
            <a href="{{ route('pendaftaran.form', $cabang->slug) }}"
               class="flex items-center justify-between bg-white rounded-2xl border border-sc-line shadow-sm px-6 py-5 hover:border-sc-teal-500 hover:shadow-md transition group">
                <div>
                    <p class="font-bold text-gray-800 text-lg group-hover:text-sc-teal-700">{{ $cabang->nama }}</p>
                    @if($cabang->alamat)
                    <p class="text-sm text-gray-400 mt-0.5">{{ $cabang->alamat }}</p>
                    @endif
                </div>
                <svg class="w-5 h-5 text-gray-300 group-hover:text-sc-teal-500 flex-shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
        </div>

        <p class="text-center text-xs text-gray-400 mt-8">
            Sudah terdaftar? <a href="{{ route('login') }}" class="text-sc-teal-600 underline">Login di sini</a>
        </p>

    </div>
</div>
@endsection
