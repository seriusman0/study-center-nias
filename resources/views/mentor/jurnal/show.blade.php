@extends('layouts.app')
@section('title', 'Jurnal: ' . $student->name . ' - Study Center Nias')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('mentor.jurnal.index') }}"
           class="w-9 h-9 rounded-xl bg-white border border-sc-line flex items-center justify-center flex-shrink-0 hover:bg-sc-teal-50 transition">
            <svg width="16" height="16" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-bold text-sc-ink-900 truncate">{{ $student->name }}</h1>
            <p class="text-xs text-sc-ink-400">{{ $student->cabang?->nama ?? '—' }} · {{ $student->username }}</p>
        </div>
        <div class="text-right flex-shrink-0">
            <span class="text-xl font-bold text-sc-teal-700">{{ $matrix['pct'] }}%</span>
            <p class="text-xs text-sc-ink-400">{{ $matrix['checked'] }}/{{ $matrix['total'] }}</p>
        </div>
    </div>

    {{-- Filter tanggal --}}
    <form method="GET" class="bg-white rounded-2xl border border-sc-line p-4 flex flex-wrap gap-3 items-end">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-sc-ink-500">Dari</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}"
                   class="rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-sc-ink-500">Sampai</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}"
                   class="rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
        </div>
        <button type="submit"
                class="px-4 py-2 bg-sc-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-sc-teal-700 transition">
            Tampilkan
        </button>
        <a href="{{ route('mentor.jurnal.export', ['student' => $student->id, 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"
           class="px-4 py-2 border border-sc-teal-300 text-sc-teal-700 text-sm font-semibold rounded-xl hover:bg-sc-teal-50 transition">
            Export CSV
        </a>
    </form>

    {{-- Matriks jurnal --}}
    <div class="bg-white rounded-2xl border border-sc-line shadow-sc-2 overflow-hidden">
        <div style="overflow-x:auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-sc-teal-50">
                        @foreach($matrix['headers'] as $h)
                        <th class="px-2 py-2 text-left font-semibold text-sc-teal-700 whitespace-nowrap border-b border-sc-line">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($matrix['rows'] as $row)
                    <tr class="border-b border-sc-line last:border-0 hover:bg-gray-50">
                        @foreach($row as $i => $cell)
                            @if($i === 0)
                            <td class="px-2 py-1.5 whitespace-nowrap text-sc-ink-600 font-medium">{{ $cell }}</td>
                            @else
                            <td class="px-2 py-1.5 text-center">
                                @if($cell === 'Y')
                                <span class="inline-flex w-5 h-5 rounded-full bg-sc-teal-500 items-center justify-center">
                                    <svg width="10" height="10" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                                @else
                                <span class="text-gray-300">–</span>
                                @endif
                            </td>
                            @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
