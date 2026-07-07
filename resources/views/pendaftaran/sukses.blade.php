@extends('layouts.app')

@section('title', 'Pendaftaran Berhasil - Study Center Nias')

@php
    $cekUrl = $username ? route('pendaftaran.cek', $username) : null;
    $qrSvg = null;
    if ($cekUrl) {
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(120)->margin(1)->generate($cekUrl);
        // Ganti width/height hardcoded → 100% agar SVG responsive dalam container
        $qrSvg = preg_replace('/(<svg[^>]*)\s+width="\d+"/', '$1 width="100%"', $qrSvg);
        $qrSvg = preg_replace('/(<svg[^>]*)\s+height="\d+"/', '$1 height="100%"', $qrSvg);
    }
@endphp

@section('content')
<div class="min-h-[90vh] flex items-center justify-center px-4 py-12 bg-gray-50">
    <div class="max-w-lg w-full">

        <div class="bg-white rounded-3xl shadow-xl shadow-gray-100 border border-gray-100 p-10 sm:p-12 text-center">

            {{-- Hero Icon --}}
            <div class="relative w-24 h-24 mx-auto mb-8">
                <div class="absolute inset-0 bg-green-100 rounded-full animate-ping opacity-25"></div>
                <div class="relative w-24 h-24 bg-gradient-to-br from-green-400 to-teal-500 rounded-full flex items-center justify-center shadow-lg">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Pendaftaran Berhasil!</h1>
            <p class="text-gray-500 text-sm leading-relaxed mb-1">
                Data Anda telah kami terima. Mohon tunggu validasi dari pengurus Study Center Nias.
            </p>
            <p class="text-gray-400 text-xs leading-relaxed mb-8">
                Setelah divalidasi, akun Anda akan diaktifkan dan bisa digunakan untuk login.
            </p>

            {{-- Download Kartu — Primary CTA --}}
            @if(session('pendaftaran_username'))
            <a href="{{ route('pendaftaran.kartu') }}"
               class="flex items-center justify-center gap-3 w-full py-4 mb-4
                      bg-sc-teal-600 hover:bg-sc-teal-700 text-white font-bold
                      rounded-2xl transition-all duration-200 shadow-md hover:shadow-lg
                      hover:-translate-y-0.5 text-sm tracking-wide">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Kartu Pendaftaran (PDF)
            </a>
            @endif

            {{-- Link Pemantauan + Copy --}}
            @if($cekUrl)
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 mb-4 text-left">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Link Pemantauan Status</p>
                <p id="cek-url" class="text-xs text-gray-700 font-mono break-all mb-4 leading-relaxed select-all">{{ $cekUrl }}</p>
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ $cekUrl }}" target="_blank" rel="noopener"
                       class="flex items-center gap-1.5 px-4 py-2 bg-sc-teal-600 hover:bg-sc-teal-700
                              text-white text-xs font-semibold rounded-xl transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka Link
                    </a>
                    <button id="copy-btn" onclick="copyLink()"
                            class="flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-gray-100
                                   border border-gray-300 text-gray-600 text-xs font-semibold
                                   rounded-xl transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span id="copy-text">Salin Link</span>
                    </button>
                </div>
            </div>

            {{-- QR Code --}}
            @if($qrSvg)
            <div class="flex items-center gap-4 bg-white border border-gray-100 rounded-2xl p-4 mb-4 text-left">
                <div class="w-28 h-28 flex-shrink-0 rounded-xl overflow-hidden border border-gray-200 bg-white p-1">
                    {!! $qrSvg !!}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800 mb-1">Scan QR Code</p>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Alternatif cek status tanpa perlu klik link. Arahkan kamera HP ke QR ini.
                    </p>
                </div>
            </div>
            @endif
            @endif

            {{-- WhatsApp Group --}}
            <div class="bg-[#f0fdf4] border border-[#bbf7d0] rounded-2xl p-5 mb-6 text-left">
                <p class="text-sm font-semibold text-gray-800 mb-1">Pantau Pengumuman</p>
                <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                    Bergabunglah ke grup WhatsApp kami untuk informasi terbaru: jadwal, aktivasi akun, dan pengumuman penting.
                </p>
                <a href="https://chat.whatsapp.com/JpNHj5H8dwPGh8BOIHbofN?mode=gi_t"
                   target="_blank" rel="noopener noreferrer"
                   style="background-color:#25D366"
                   class="inline-flex items-center gap-2 text-white text-xs font-semibold px-6 py-3 rounded-xl hover:opacity-90 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Bergabung ke Grup WhatsApp
                </a>
            </div>

            <a href="{{ route('home') }}" class="text-sm text-gray-400 hover:text-sc-teal-600 transition">
                ← Kembali ke Beranda
            </a>

        </div>
    </div>
</div>

<script>
function copyLink() {
    const url = document.getElementById('cek-url').innerText.trim();
    const btn = document.getElementById('copy-btn');
    const text = document.getElementById('copy-text');
    navigator.clipboard.writeText(url).then(() => {
        text.textContent = 'Tersalin!';
        btn.classList.add('bg-green-50', 'border-green-300', 'text-green-700');
        btn.classList.remove('border-gray-300', 'text-gray-600');
        setTimeout(() => {
            text.textContent = 'Salin Link';
            btn.classList.remove('bg-green-50', 'border-green-300', 'text-green-700');
            btn.classList.add('border-gray-300', 'text-gray-600');
        }, 2000);
    }).catch(() => {
        // fallback untuk browser lama
        const el = document.createElement('textarea');
        el.value = url;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        text.textContent = 'Tersalin!';
        setTimeout(() => { text.textContent = 'Salin Link'; }, 2000);
    });
}
</script>
@endsection
