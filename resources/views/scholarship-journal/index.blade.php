@extends('layouts.app')

@section('title', 'Jurnal Mahasiswa')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="bg-gradient-to-br from-sc-teal-700 to-sc-teal-600 text-white shadow-sc-3 rounded-2xl p-5 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-2xl">Jurnal Kegiatan</h1>
                <p class="text-sm text-white/80 mt-1">Rekam perkembangan akademik & pelayananmu</p>
            </div>
            <a href="{{ route('scholarship-journal.create') }}"
               class="bg-white text-sc-teal-700 font-bold text-sm px-4 py-2 rounded-xl hover:bg-sc-teal-50 transition">
                + Buat Jurnal
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
        $statItems = [
            ['label' => 'Disetujui', 'value' => $stats['approved'], 'color' => 'text-green-600', 'bg' => 'bg-green-50 border-green-200'],
            ['label' => 'Menunggu', 'value' => $stats['pending'], 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-50 border-yellow-200'],
            ['label' => 'Draft', 'value' => $stats['draft'], 'color' => 'text-sc-ink-500', 'bg' => 'bg-sc-ink-50 border-sc-line'],
            ['label' => 'Perlu Revisi', 'value' => $stats['revision'], 'color' => 'text-red-600', 'bg' => 'bg-red-50 border-red-200'],
        ];
        @endphp
        @foreach($statItems as $s)
        <div class="border {{ $s['bg'] }} rounded-xl p-4 text-center">
            <div class="text-2xl font-bold {{ $s['color'] }}">{{ $s['value'] }}</div>
            <div class="text-xs text-sc-ink-500 mt-1">{{ $s['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Journal list --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-sc-line">
            <h2 class="font-bold text-sc-ink-900">Riwayat Jurnal</h2>
        </div>

        @if($journals->isEmpty())
        <div class="px-5 py-10 text-center text-sc-ink-400 text-sm">
            Belum ada jurnal. <a href="{{ route('scholarship-journal.create') }}" class="text-sc-teal-600 font-semibold">Buat sekarang</a>.
        </div>
        @else
        <div class="divide-y divide-sc-line">
            @foreach($journals as $journal)
            @php
            $statusColor = match($journal->status) {
                'approved' => 'bg-green-100 text-green-700',
                'submitted', 'under_review' => 'bg-yellow-100 text-yellow-700',
                'revision_required' => 'bg-red-100 text-red-700',
                default => 'bg-sc-ink-100 text-sc-ink-500',
            };
            @endphp
            <div class="px-5 py-4 flex items-center justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sc-ink-900 text-sm truncate">{{ $journal->title }}</div>
                    <div class="text-xs text-sc-ink-400 mt-0.5">{{ $journal->created_at->format('d M Y') }}</div>
                </div>
                <span class="shrink-0 text-xs font-semibold px-3 py-1 rounded-full {{ $statusColor }}">
                    {{ $journal->statusLabel }}
                </span>
                <div class="shrink-0 flex gap-2">
                    <a href="{{ route('scholarship-journal.show', $journal->id) }}"
                       class="text-xs text-sc-teal-600 font-semibold hover:underline">Lihat</a>
                    @if($journal->isEditable())
                    <a href="{{ route('scholarship-journal.edit', $journal->id) }}"
                       class="text-xs text-sc-ink-400 hover:text-sc-ink-700 font-semibold">Edit</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        {{ $journals->links() }}
        @endif
    </div>
</div>
@endsection
