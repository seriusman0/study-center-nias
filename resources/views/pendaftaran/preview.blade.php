@extends('layouts.app')

@section('title', 'Periksa Data Pendaftaran')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-3xl mx-auto">

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-full mb-3">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Periksa Data Sebelum Mengirim</h1>
            <p class="text-gray-500 text-sm mt-1">Pastikan semua data di bawah sudah benar. Data yang sudah dikirim tidak dapat diubah sendiri.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-sc-line overflow-hidden mb-6">

            {{-- Header kartu --}}
            <div class="bg-sc-teal-600 px-6 py-4">
                <p class="text-white text-xs font-semibold tracking-widest uppercase opacity-80">Pratinjau Data Pendaftaran · {{ $cabang->nama }}</p>
                <p class="text-white font-bold text-lg">{{ $data['name'] }}</p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-[auto_1fr] gap-8">

                {{-- Foto --}}
                <div class="text-center">
                    <div class="w-36 aspect-[3/4] mx-auto overflow-hidden rounded-xl border-2 border-sc-line shadow-sm">
                        <img src="{{ asset('storage/' . $fotoTemp) }}"
                             alt="Foto {{ $data['name'] }}"
                             class="w-full h-full object-cover">
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Foto untuk sertifikat</p>
                </div>

                {{-- Data --}}
                <div>
                    <div class="text-sm" style="display:grid; grid-template-columns: max-content auto 1fr; gap: 0.625rem 0.75rem; align-items: start;">

                        <span class="text-gray-400 text-xs font-medium uppercase tracking-wide">Nama Lengkap</span>
                        <span class="text-gray-300">:</span>
                        <span class="font-semibold text-gray-800">{{ $data['name'] }}</span>

                        <span class="text-gray-400 text-xs font-medium uppercase tracking-wide">Jenis Kelamin</span>
                        <span class="text-gray-300">:</span>
                        <span class="text-gray-800">{{ $data['gender'] === 'laki-laki' ? 'Laki-laki' : 'Perempuan' }}</span>

                        <span class="text-gray-400 text-xs font-medium uppercase tracking-wide">Tanggal Lahir</span>
                        <span class="text-gray-300">:</span>
                        <span class="text-gray-800">{{ \Carbon\Carbon::parse($data['birth_date'])->isoFormat('D MMMM YYYY') }}</span>

                        <span class="text-gray-400 text-xs font-medium uppercase tracking-wide">Nama Sekolah</span>
                        <span class="text-gray-300">:</span>
                        <span class="text-gray-800">{{ $data['school_name'] }}</span>

                        <span class="text-gray-400 text-xs font-medium uppercase tracking-wide">Kelas</span>
                        <span class="text-gray-300">:</span>
                        <span class="text-gray-800">{{ $data['grade_class'] }}</span>

                        <span class="text-gray-400 text-xs font-medium uppercase tracking-wide">Alamat</span>
                        <span class="text-gray-300">:</span>
                        <span class="text-gray-800">{{ $data['address'] }}</span>

                        <span class="text-gray-400 text-xs font-medium uppercase tracking-wide">HP Orangtua/Wali</span>
                        <span class="text-gray-300">:</span>
                        <span class="text-gray-800">{{ $data['guardian_phone'] }}</span>

                        @if($data['student_phone'])
                        <span class="text-gray-400 text-xs font-medium uppercase tracking-wide">HP Siswa</span>
                        <span class="text-gray-300">:</span>
                        <span class="text-gray-800">{{ $data['student_phone'] }}</span>
                        @endif

                        @if($data['note'])
                        <span class="text-gray-400 text-xs font-medium uppercase tracking-wide">Catatan Jadwal</span>
                        <span class="text-gray-300">:</span>
                        <span class="text-gray-800 italic">{{ $data['note'] }}</span>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- Aksi --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('pendaftaran.form', $cabang->slug) }}"
               class="flex-1 text-center py-3 border-2 border-sc-teal-600 text-sc-teal-700 rounded-xl font-semibold hover:bg-sc-teal-50 transition text-sm">
                ← Perbaiki Data
            </a>
            <form method="POST" action="{{ route('pendaftaran.konfirmasi', $cabang->slug) }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full py-3 bg-sc-teal-600 text-white rounded-xl font-semibold hover:bg-sc-teal-700 transition text-sm">
                    Data Sudah Benar — Kirim Pendaftaran ✓
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            Dengan mengirim, Anda menyetujui bahwa data di atas adalah benar dan dapat dipertanggungjawabkan.
        </p>

    </div>
</div>
@endsection
