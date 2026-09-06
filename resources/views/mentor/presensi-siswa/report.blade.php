@extends('layouts.app')
@section('title', 'Laporan Presensi Siswa - Study Center Nias')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('presensi.index', request()->query()) }}"
           class="w-9 h-9 rounded-xl bg-white border border-sc-line flex items-center justify-center flex-shrink-0 hover:bg-gray-50 transition">
            <svg width="16" height="16" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <h1 class="text-xl font-bold text-sc-ink-900">Laporan Lengkap Presensi</h1>
    </div>

    {{-- Filter: single row compact --}}
    <form method="GET" id="filterForm" class="bg-white rounded-2xl border border-sc-line p-4">
        <div class="flex flex-wrap gap-2 items-center">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kelas / materi..."
                   class="rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500 min-w-0 flex-1" style="min-width:140px">
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                   class="rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
            <span class="text-xs text-sc-ink-400 flex-shrink-0">s/d</span>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                   class="rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
            <button type="submit"
                    style="padding:8px 16px;background:#007a5c;color:#fff;font-size:.8125rem;font-weight:600;border:none;border-radius:10px;cursor:pointer;white-space:nowrap;">
                Filter
            </button>
            <a href="{{ route('presensi.report') }}"
               style="padding:8px 16px;border:1.5px solid #d1d5db;background:#fff;color:#374151;font-size:.8125rem;font-weight:500;border-radius:10px;text-decoration:none;white-space:nowrap;">
                Reset
            </a>
        </div>
    </form>

    {{-- Stat cards with icons --}}
    <div class="grid grid-cols-3 gap-3">

        @php
        $stats = [
            ['label'=>'Total Kelas',  'value'=>$summary['total_kelas'],  'color'=>'#374151', 'bg'=>'#f3f4f6', 'icon'=>'<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>'],
            ['label'=>'Hadir',        'value'=>$summary['hadir'],        'color'=>'#16a34a', 'bg'=>'#f0fdf4', 'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
            ['label'=>'Siswa Unik',   'value'=>$summary['total_siswa'],  'color'=>'#2563eb', 'bg'=>'#eff6ff', 'icon'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['label'=>'Izin',         'value'=>$summary['izin'],         'color'=>'#d97706', 'bg'=>'#fffbeb', 'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'],
            ['label'=>'Sakit',        'value'=>$summary['sakit'],        'color'=>'#0891b2', 'bg'=>'#ecfeff', 'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>'],
            ['label'=>'Alpha',        'value'=>$summary['alpha'],        'color'=>'#dc2626', 'bg'=>'#fef2f2', 'icon'=>'<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'],
        ];
        @endphp

        @foreach($stats as $s)
        <div class="bg-white rounded-2xl border border-sc-line p-4 flex flex-col items-center gap-2">
            <div style="width:36px;height:36px;border-radius:10px;background:{{ $s['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="{{ $s['color'] }}" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $s['icon'] !!}</svg>
            </div>
            <p class="text-2xl font-bold leading-none" style="color:{{ $s['color'] }}">{{ $s['value'] }}</p>
            <p class="text-xs text-sc-ink-400 text-center leading-tight">{{ $s['label'] }}</p>
        </div>
        @endforeach

    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-sc-line overflow-hidden">
        <div class="px-4 py-3 border-b border-sc-line">
            <p class="text-sm font-semibold text-sc-ink-700">{{ $rows->total() }} baris ditemukan</p>
        </div>

        @if($rows->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 px-4">
            <svg width="64" height="64" fill="none" viewBox="0 0 64 64" class="mb-4 opacity-30">
                <rect x="8" y="8" width="48" height="48" rx="8" fill="#e5e7eb"/>
                <circle cx="32" cy="28" r="10" stroke="#9ca3af" stroke-width="2.5" fill="none"/>
                <line x1="39" y1="35" x2="50" y2="46" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="22" y1="28" x2="26" y2="28" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
                <line x1="32" y1="22" x2="32" y2="34" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <p class="text-sm font-semibold text-sc-ink-500">Tidak ada data ditemukan</p>
            <p class="text-xs text-sc-ink-400 mt-1">Coba ubah filter atau reset pencarian</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-sc-line">
                    <tr>
                        <th class="px-3 py-3 text-left font-semibold text-sc-ink-500 uppercase tracking-wide" style="font-size:10px">Siswa</th>
                        <th class="px-3 py-3 text-left font-semibold text-sc-ink-500 uppercase tracking-wide" style="font-size:10px">Kelas</th>
                        <th class="px-3 py-3 text-left font-semibold text-sc-ink-500 uppercase tracking-wide" style="font-size:10px">Tanggal</th>
                        <th class="px-3 py-3 text-center font-semibold text-sc-ink-500 uppercase tracking-wide" style="font-size:10px">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sc-line">
                    @foreach($rows as $r)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-3 py-2.5 font-medium text-sc-ink-900">{{ $r->siswa_name }}</td>
                        <td class="px-3 py-2.5 text-sc-ink-600">{{ $r->kelas }}</td>
                        <td class="px-3 py-2.5 text-sc-ink-400">{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</td>
                        <td class="px-3 py-2.5 text-center">
                            @php
                                $badge = ['hadir'=>'bg-green-100 text-green-700','izin'=>'bg-yellow-100 text-yellow-700','sakit'=>'bg-blue-100 text-blue-700','alpha'=>'bg-red-100 text-red-700'][$r->status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="inline-block px-2.5 py-0.5 rounded-full font-semibold {{ $badge }}" style="font-size:11px">{{ ucfirst($r->status) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($rows->lastPage() > 1)
        <div class="px-4 py-3 border-t border-sc-line">{{ $rows->withQueryString()->links() }}</div>
        @endif
        @endif
    </div>

</div>
@endsection
