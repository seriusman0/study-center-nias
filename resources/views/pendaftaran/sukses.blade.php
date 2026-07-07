@extends('layouts.app')

@section('title', 'Pendaftaran Diterima - Study Center Nias')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full text-center">

        <div class="bg-white rounded-2xl shadow-sm border border-sc-line p-10">

            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-sc-teal-800 mb-3">Pendaftaran Berhasil Dikirim!</h1>

            <p class="text-gray-600 text-sm leading-relaxed mb-2">
                Data pendaftaran Anda telah kami terima. Mohon bersabar menunggu validasi data dari pengurus Study Center Nias.
            </p>
            <p class="text-gray-500 text-sm leading-relaxed mb-6">
                Setelah data divalidasi, akun Anda akan diaktifkan dan Anda dapat masuk ke sistem.
            </p>

            {{-- Download Kartu --}}
            @if(session('pendaftaran_username'))
            <a href="{{ route('pendaftaran.kartu') }}"
               class="flex items-center justify-center gap-2 w-full py-3 mb-3 bg-sc-teal-600 hover:bg-sc-teal-700 text-white font-semibold rounded-xl transition text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Download Kartu Pendaftaran (PDF)
            </a>
            @endif

            {{-- Cek Status --}}
            @if($username)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-5 text-left">
                <p class="text-xs font-semibold text-gray-600 mb-1">Link Pemantauan Status Pendaftaran</p>
                <p class="text-xs text-gray-500 mb-2">Simpan link berikut untuk memantau apakah pendaftaran Anda diterima, ditolak, atau perlu perbaikan.</p>
                <a href="{{ route('pendaftaran.cek', $username) }}"
                   class="block text-xs text-sc-teal-600 hover:underline break-all font-mono">
                    {{ route('pendaftaran.cek', $username) }}
                </a>
            </div>
            @endif

            {{-- WhatsApp Group --}}
            <div class="bg-[#dcf8c6] border border-[#b5e7a0] rounded-xl p-5 mb-6">
                <p class="text-sm font-semibold text-gray-800 mb-1">Pantau Pengumuman</p>
                <p class="text-xs text-gray-600 mb-4">
                    Bergabunglah ke grup WhatsApp kami untuk mendapatkan informasi terbaru mengenai jadwal, aktivasi akun, dan pengumuman penting.
                </p>
                <a href="https://chat.whatsapp.com/JpNHj5H8dwPGh8BOIHbofN?mode=gi_t"
                   target="_blank" rel="noopener noreferrer"
                   style="background-color:#25D366"
                   class="inline-flex items-center gap-2 text-white text-sm font-semibold px-5 py-3 rounded-xl hover:opacity-90 transition">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Bergabung ke Grup WhatsApp
                </a>
            </div>

            <a href="{{ route('home') }}" class="text-sm text-sc-teal-600 hover:underline">
                Kembali ke Beranda
            </a>

        </div>
    </div>
</div>
@endsection
