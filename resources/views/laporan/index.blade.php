@extends('layouts.app')
@section('title', 'Laporan – Study Center Nias')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 space-y-5">

    {{-- Header + badge role --}}
    <div class="flex items-center justify-between gap-2">
        <h1 class="text-xl font-bold text-sc-ink-900">Laporan Jurnal</h1>
        @if(isset($jurnalRoles) && count($jurnalRoles) > 0)
        <div class="flex flex-wrap gap-1 justify-end">
            @foreach($jurnalRoles as $role)
            @php
                $roleColors = [
                    'student'              => 'bg-blue-100 text-blue-700 border-blue-200',
                    'scholarship_teenager' => 'bg-purple-100 text-purple-700 border-purple-200',
                    'college'              => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                ];
                $roleLabels = [
                    'student'              => 'Siswa',
                    'scholarship_teenager' => 'Remaja Beasiswa',
                    'college'              => 'College',
                ];
                $colorClass = $roleColors[$role] ?? 'bg-sc-ink-100 text-sc-ink-600 border-sc-line';
                $roleLabel  = $roleLabels[$role] ?? ucfirst($role);
            @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $colorClass }}">
                {{ $roleLabel }}
            </span>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Info multi-role --}}
    @if(isset($dailyMatrix['multi_role']) && $dailyMatrix['multi_role'])
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-blue-700">
            Kamu memiliki <strong>{{ count($jurnalRoles) }} program jurnal</strong>
            ({{ implode(' + ', $dailyMatrix['role_labels']) }}).
            Progress harian di bawah menggabungkan semua item dari kedua program.
        </p>
    </div>
    @endif

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

    {{-- ============================================================ --}}
    {{-- PROGRESS HARIAN (detail per hari + poin)                     --}}
    {{-- ============================================================ --}}
    <div class="bg-white rounded-2xl border border-sc-line shadow-sc-1">
        <div class="px-5 py-4 border-b border-sc-line">
            <div class="flex items-center justify-between gap-2 mb-3">
                <h2 class="font-semibold text-sc-ink-900 text-sm">Progress Harian</h2>
                @if(isset($dailyMatrix['multi_role']) && $dailyMatrix['multi_role'])
                <span class="text-xs text-sc-ink-400">Gabungan semua program</span>
                @endif
            </div>
            {{-- Filter tanggal --}}
            <form method="GET" action="{{ route('laporan') }}" class="flex items-center gap-1 text-xs">
                <input type="date" name="from"
                       value="{{ $dailyFrom->toDateString() }}"
                       class="border border-sc-line rounded-lg px-2 py-1 text-xs text-sc-ink-700 focus:outline-none focus:ring-1 focus:ring-sc-teal-500">
                <span class="text-sc-ink-400">–</span>
                <input type="date" name="to"
                       value="{{ $dailyTo->toDateString() }}"
                       class="border border-sc-line rounded-lg px-2 py-1 text-xs text-sc-ink-700 focus:outline-none focus:ring-1 focus:ring-sc-teal-500">
                <button type="submit"
                        class="bg-sc-teal-600 text-white rounded-lg px-3 py-1 text-xs font-semibold hover:bg-sc-teal-700 transition-colors">
                    Tampilkan
                </button>
            </form>
        </div>

        @if(count($dailyMatrix['days']) === 0)
        <div class="px-5 py-8 text-center text-sm text-sc-ink-400">
            Belum ada data jurnal di rentang tanggal ini.
        </div>
        @else
        <div class="divide-y divide-sc-line rounded-b-2xl overflow-hidden">
            @foreach($dailyMatrix['days'] as $dayIdx => $day)
            {{-- Accordion per hari — Alpine.js toggle (bukan <details> agar reliable di semua browser) --}}
            <div x-data="{ open: false }" class="bg-white">
                {{-- Header row (klik untuk buka/tutup) --}}
                <button type="button"
                        @click="open = !open"
                        class="w-full px-5 py-3 flex items-center gap-3 text-left hover:bg-sc-teal-50 transition-colors">
                    {{-- Ikon chevron --}}
                    <svg class="w-4 h-4 text-sc-ink-400 flex-shrink-0 transition-transform duration-200"
                         :class="open ? 'rotate-90' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-sc-ink-800 truncate">{{ $day['label'] }}</span>
                            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                {{-- Points badge --}}
                                <span class="inline-flex items-center gap-1 bg-sc-teal-50 border border-sc-teal-200 rounded-full px-2 py-0.5 text-xs font-bold text-sc-teal-700">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    {{ $day['points'] }} poin
                                </span>
                                {{-- Pct --}}
                                <span class="text-xs font-semibold {{ $day['pct'] >= 60 ? 'text-sc-teal-700' : ($day['pct'] >= 30 ? 'text-sc-orange-600' : 'text-sc-ink-400') }}">
                                    {{ $day['pct'] }}%
                                </span>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div class="w-full bg-sc-line rounded-full h-1.5 overflow-hidden">
                            <div class="h-1.5 rounded-full transition-all
                                {{ $day['pct'] >= 60 ? 'bg-sc-teal-600' : ($day['pct'] >= 30 ? 'bg-sc-orange-500' : 'bg-sc-ink-300') }}"
                                 style="width: {{ $day['pct'] }}%"></div>
                        </div>
                        <div class="text-xs text-sc-ink-400 mt-0.5">{{ $day['checked'] }} / {{ $day['total'] }} item selesai</div>
                    </div>
                </button>

                {{-- Detail checklist item per hari --}}
                <div x-show="open" x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-5 pb-4 pt-2 bg-sc-teal-50 border-t border-sc-line"
                     style="display:none">
                    @php
                        $multiRole = $dailyMatrix['multi_role'] ?? false;
                        $grouped   = collect($day['cells'])->groupBy(fn($c) => $c['kategori'] ?? '__plpb');
                        $kategoriLabels = [
                            '__plpb'     => 'Pembacaan Alkitab',
                            'kerohanian' => 'Kerohanian',
                            'pendidikan' => 'Pendidikan',
                            'karakter'   => 'Karakter',
                            'pembacaan'  => 'Pembacaan',
                            'sidang'     => 'Sidang',
                            'rohani'     => 'Rohani',
                            'prajurit'   => 'Prajurit',
                        ];
                    @endphp

                    @if($multiRole)
                        @foreach($grouped as $kat => $cells)
                        <div class="mb-3 last:mb-0">
                            <div class="text-xs font-bold text-sc-teal-700 uppercase tracking-wider mb-1.5">
                                {{ $kategoriLabels[$kat] ?? ucfirst($kat) }}
                            </div>
                            <div class="space-y-1.5 pl-1">
                                @foreach($cells as $cell)
                                <div class="flex items-center gap-2">
                                    @if($cell['ok'])
                                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-sc-teal-500 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                    @else
                                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-white border border-sc-line flex items-center justify-center">
                                            <span class="w-2 h-2 rounded-full bg-sc-ink-300"></span>
                                        </span>
                                    @endif
                                    <span class="text-sm {{ $cell['ok'] ? 'text-sc-ink-800 font-medium' : 'text-sc-ink-400' }} flex-1">
                                        {{ $cell['label'] }}
                                        @if($cell['ok'] && ($cell['value'] ?? null) !== null)
                                            <span class="text-xs text-sc-teal-600 ml-1">({{ $cell['value'] }})</span>
                                        @endif
                                    </span>
                                    @if($cell['ok'])
                                        <span class="text-xs text-sc-teal-600 font-semibold flex-shrink-0">+1</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="space-y-1.5">
                            @foreach($day['cells'] as $cell)
                            <div class="flex items-center gap-2">
                                @if($cell['ok'])
                                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-sc-teal-500 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                @else
                                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-white border border-sc-line flex items-center justify-center">
                                        <span class="w-2 h-2 rounded-full bg-sc-ink-300"></span>
                                    </span>
                                @endif
                                <span class="text-sm {{ $cell['ok'] ? 'text-sc-ink-800 font-medium' : 'text-sc-ink-400' }} flex-1">
                                    {{ $cell['label'] }}
                                    @if($cell['ok'] && ($cell['value'] ?? null) !== null)
                                        <span class="text-xs text-sc-teal-600 ml-1">({{ $cell['value'] }})</span>
                                    @endif
                                </span>
                                @if($cell['ok'])
                                    <span class="text-xs text-sc-teal-600 font-semibold flex-shrink-0">+1 poin</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    {{-- ============================================================ --}}

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
                        <img src="{{ $u->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($u->name).'&background=007a5c&color=fff' }}"
                             alt="{{ $u->name }}"
                             class="w-8 h-8 rounded-full border border-sc-line object-cover">
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
