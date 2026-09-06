@extends('layouts.app')
@section('title', 'Jurnal Siswa - Study Center Nias')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-sc-ink-900">Jurnal Siswa</h1>
            <p class="text-xs text-sc-ink-400 mt-0.5">{{ auth()->user()->cabang?->nama ?? 'Cabang Anda' }}</p>
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" class="flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Cari nama siswa..."
               class="flex-1 rounded-xl border border-sc-line px-4 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
        <button type="submit"
                class="px-4 py-2 bg-sc-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-sc-teal-700 transition">
            Cari
        </button>
    </form>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-sc-line shadow-sc-2 p-4 text-center">
            <p class="text-2xl font-bold text-sc-teal-700">{{ $totalToday }}</p>
            <p class="text-xs text-sc-ink-400 mt-1">Isi jurnal hari ini</p>
        </div>
        <div class="bg-white rounded-2xl border border-sc-line shadow-sc-2 p-4 text-center">
            <p class="text-2xl font-bold text-sc-teal-700">{{ $totalWeek }}</p>
            <p class="text-xs text-sc-ink-400 mt-1">Entry 7 hari terakhir</p>
        </div>
    </div>

    {{-- Daftar Siswa --}}
    <div class="bg-white rounded-2xl border border-sc-line shadow-sc-2 overflow-hidden">
        @forelse($students as $s)
        <a href="{{ route('mentor.jurnal.show', $s) }}"
           class="flex items-center gap-3 px-4 py-3 border-b border-sc-line last:border-0 hover:bg-sc-teal-50 transition group">
            <div class="w-9 h-9 rounded-full bg-sc-teal-100 flex items-center justify-center flex-shrink-0 text-sc-teal-700 font-bold text-sm">
                {{ strtoupper(substr($s->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-sc-ink-900 truncate group-hover:text-sc-teal-700">{{ $s->name }}</p>
                <p class="text-xs text-sc-ink-400">{{ $s->cabang?->nama ?? '—' }}</p>
            </div>
            <svg width="16" height="16" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        @empty
        <div class="px-4 py-10 text-center text-sm text-sc-ink-400">
            Tidak ada siswa ditemukan.
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div>{{ $students->withQueryString()->links() }}</div>

</div>
@endsection
