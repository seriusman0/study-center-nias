@extends('layouts.app')
@section('title', 'Detail Presensi - Study Center Nias')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('presensi.index') }}"
           class="w-9 h-9 rounded-xl bg-white border border-sc-line flex items-center justify-center flex-shrink-0 hover:bg-gray-50 transition">
            <svg width="16" height="16" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-bold text-sc-ink-900">{{ $presensi->kelas }}</h1>
            <p class="text-xs text-sc-ink-400">{{ $presensi->tanggal->format('d M Y') }} · {{ substr($presensi->jam_mulai,0,5) }}–{{ substr($presensi->jam_selesai,0,5) }}</p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <a href="{{ route('presensi.edit', $presensi->id) }}"
               class="px-3 py-1.5 text-xs font-medium text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-50 transition">Edit</a>
            <form method="POST" action="{{ route('presensi.destroy', $presensi->id) }}" onsubmit="return confirm('Hapus presensi ini?')" class="inline">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">Hapus</button>
            </form>
        </div>
    </div>

    {{-- Detail sesi --}}
    <div class="bg-white rounded-2xl border border-sc-line shadow-sc-2 p-5 space-y-3">
        <h2 class="text-sm font-bold text-sc-ink-700 uppercase tracking-wide">Detail Sesi</h2>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <p class="text-xs text-sc-ink-400">Mentor</p>
                <p class="font-semibold text-sc-ink-900">{{ $presensi->mentor?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-sc-ink-400">Cabang</p>
                <p class="font-semibold text-sc-ink-900">{{ $presensi->cabang?->nama ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-xs text-sc-ink-400">Materi</p>
                <p class="text-sc-ink-800 leading-relaxed whitespace-pre-wrap">{{ $presensi->materi }}</p>
            </div>
        </div>
    </div>

    @if($presensi->foto)
    <div class="bg-white rounded-2xl border border-sc-line shadow-sc-2 overflow-hidden">
        <a href="{{ asset('storage/' . $presensi->foto) }}" target="_blank">
            <img src="{{ asset('storage/' . $presensi->foto) }}" class="w-full object-cover max-h-64" alt="Foto">
        </a>
    </div>
    @endif

    {{-- Siswa --}}
    @php
        $hadir = $presensi->students->where('pivot.status','hadir')->count();
        $izin  = $presensi->students->where('pivot.status','izin')->count();
        $sakit = $presensi->students->where('pivot.status','sakit')->count();
        $alpha = $presensi->students->where('pivot.status','alpha')->count();
    @endphp
    <div class="bg-white rounded-2xl border border-sc-line shadow-sc-2 overflow-hidden">
        <div class="px-4 py-3 border-b border-sc-line flex items-center justify-between flex-wrap gap-2">
            <p class="text-sm font-bold text-sc-ink-900">Siswa ({{ $presensi->students->count() }})</p>
            <div class="flex gap-2">
                @if($hadir)<span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">Hadir {{ $hadir }}</span>@endif
                @if($izin)<span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">Izin {{ $izin }}</span>@endif
                @if($sakit)<span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">Sakit {{ $sakit }}</span>@endif
                @if($alpha)<span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">Alpha {{ $alpha }}</span>@endif
            </div>
        </div>
        @foreach($presensi->students as $i => $s)
        @php
            $st = $s->pivot->status;
            $stColor = ['hadir'=>'text-green-700 bg-green-100','izin'=>'text-yellow-700 bg-yellow-100','sakit'=>'text-blue-700 bg-blue-100','alpha'=>'text-red-700 bg-red-100'][$st] ?? 'text-gray-600 bg-gray-100';
        @endphp
        <div class="flex items-center gap-3 px-4 py-3 border-b border-sc-line last:border-0">
            <span class="w-6 text-xs text-sc-ink-400 flex-shrink-0 text-center">{{ $i+1 }}</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-sc-ink-900">{{ $s->name }}</p>
                <p class="text-xs text-sc-ink-400">{{ $s->studentProfile?->grade_class ?? '—' }} · {{ $s->cabang?->nama ?? '—' }}</p>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $stColor }}">{{ ucfirst($st) }}</span>
        </div>
        @endforeach
        @if($presensi->students->isEmpty())
        <div class="px-4 py-8 text-center text-sm text-sc-ink-400">Belum ada siswa.</div>
        @endif
    </div>

</div>
@endsection
