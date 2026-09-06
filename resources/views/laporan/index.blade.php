@extends('layouts.app')
@section('title', 'Laporan – Study Center Nias')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 space-y-5">

    <h1 class="text-xl font-bold text-sc-ink-900">Laporan Jurnal</h1>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-sc-line shadow-sc-1 p-4 text-center">
            <div class="text-3xl font-extrabold text-sc-teal-700">{{ $overallActiveDays }}</div>
            <div class="text-xs text-sc-ink-500 mt-1">Total Hari Aktif</div>
        </div>
        <div class="bg-white rounded-2xl border border-sc-line shadow-sc-1 p-4 text-center">
            <div class="text-3xl font-extrabold text-sc-teal-700">{{ $bestWeek ? $bestWeek['pct'] : 0 }}%</div>
            <div class="text-xs text-sc-ink-500 mt-1">Minggu Terbaik</div>
        </div>
    </div>

    {{-- Weekly breakdown --}}
    <div class="bg-white rounded-2xl border border-sc-line shadow-sc-1 overflow-hidden">
        <div class="px-5 py-4 border-b border-sc-line">
            <h2 class="font-semibold text-sc-ink-900 text-sm">Jurnal 8 Minggu Terakhir</h2>
        </div>
        <div class="divide-y divide-sc-line">
            @foreach($weeks as $week)
            <div class="px-5 py-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-sc-ink-500">{{ $week['label'] }}</span>
                    <span class="text-xs font-semibold {{ $week['pct'] >= 60 ? 'text-sc-teal-700' : ($week['pct'] >= 30 ? 'text-sc-orange-600' : 'text-sc-ink-400') }}">
                        {{ $week['pct'] }}%
                    </span>
                </div>
                <div class="w-full bg-sc-line rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full transition-all
                        {{ $week['pct'] >= 60 ? 'bg-sc-teal-600' : ($week['pct'] >= 30 ? 'bg-sc-orange-500' : 'bg-sc-ink-300') }}"
                         style="width: {{ $week['pct'] }}%"></div>
                </div>
                <div class="text-xs text-sc-ink-400 mt-1">
                    {{ $week['checks'] }} item tercatat · {{ $week['active_days'] }} hari aktif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Leaderboard --}}
    @if(isset($leaderboards) && count($leaderboards) > 0)
    <div class="space-y-4">
        @foreach($leaderboards as $roleLabel => $rankedUsers)
        <div class="bg-white rounded-2xl border border-sc-line shadow-sc-1 overflow-hidden">
            <div class="px-5 py-4 border-b border-sc-line">
                <h2 class="font-semibold text-sc-ink-900 text-sm">Leaderboard {{ $roleLabel }} (7 Hari Terakhir)</h2>
            </div>
            <div class="divide-y divide-sc-line">
                @foreach($rankedUsers as $index => $u)
                <div class="px-5 py-3 flex items-center justify-between {{ $u->id === $user->id ? 'bg-sc-teal-50/50' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-6 text-center text-sm font-bold {{ $index < 3 ? 'text-sc-teal-700' : 'text-sc-ink-400' }}">
                            #{{ $index + 1 }}
                        </div>
                        <img src="{{ $u->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($u->name).'&background=007a5c&color=fff' }}" alt="{{ $u->name }}" class="w-8 h-8 rounded-full border border-sc-line object-cover">
                        <span class="text-sm font-medium {{ $u->id === $user->id ? 'text-sc-teal-700' : 'text-sc-ink-700' }}">{{ $u->name }}</span>
                    </div>
                    <div class="text-sm font-bold text-sc-ink-900">
                        {{ $u->score }} <span class="text-xs font-normal text-sc-ink-500">pts</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <p class="text-xs text-sc-ink-400 text-center pb-2">
        Data jurnal {{ $user->name }} · {{ now()->format('d M Y') }}
    </p>
</div>
@endsection
