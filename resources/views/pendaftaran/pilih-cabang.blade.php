@extends('layouts.app')

@section('title', 'Pendaftaran Siswa - Study Center Nias')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-sc-teal-800">Pendaftaran Siswa Baru</h1>
            <p class="text-sc-ink-500 mt-2">Pilih cabang Study Center terdekat untuk melanjutkan pendaftaran.</p>
        </div>

        @if(session('info'))
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-800 text-sm">
            {{ session('info') }}
        </div>
        @endif

        <div class="grid gap-4">
            @foreach($cabangs as $cabang)
                @if($cabang->pendaftaran_buka)
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
                @else
                <div class="flex items-center justify-between bg-gray-50 rounded-2xl border border-gray-200 px-6 py-5 opacity-60 cursor-not-allowed">
                    <div>
                        <p class="font-bold text-gray-500 text-lg">{{ $cabang->nama }}</p>
                        @if($cabang->alamat)
                        <p class="text-sm text-gray-400 mt-0.5">{{ $cabang->alamat }}</p>
                        @endif
                        <span class="inline-block mt-1 text-xs font-semibold text-red-500 bg-red-50 border border-red-200 rounded-full px-2 py-0.5">Pendaftaran Ditutup</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-300 flex-shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                @endif
            @endforeach
        </div>

        <p class="text-center text-xs text-gray-400 mt-8">
            Sudah terdaftar? <a href="{{ route('login') }}" class="text-sc-teal-600 underline">Login di sini</a>
        </p>

    </div>
</div>
@endsection
