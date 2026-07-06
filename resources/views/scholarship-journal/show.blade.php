@extends('layouts.app')

@section('title', $journal->title)

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    <div class="mb-5 flex items-center gap-3">
        <a href="{{ route('scholarship-journal.index') }}" class="text-sc-ink-400 hover:text-sc-ink-700 text-sm">&larr; Kembali</a>
        <h1 class="font-display text-xl text-sc-ink-900 flex-1">{{ $journal->title }}</h1>
        @php
        $statusColor = match($journal->status) {
            'approved' => 'bg-green-100 text-green-700',
            'submitted', 'under_review' => 'bg-yellow-100 text-yellow-700',
            'revision_required' => 'bg-red-100 text-red-700',
            default => 'bg-sc-ink-100 text-sc-ink-500',
        };
        @endphp
        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $statusColor }}">{{ $journal->statusLabel }}</span>
    </div>

    @if($journal->status === 'revision_required' && $journal->reviewer_notes)
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="text-xs font-bold text-red-600 mb-1">Catatan Revisi dari Koordinator</div>
        <p class="text-sm text-red-800">{{ $journal->reviewer_notes }}</p>
        <a href="{{ route('scholarship-journal.edit', $journal->id) }}"
           class="inline-block mt-3 px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700">
            Perbaiki & Kirim Ulang
        </a>
    </div>
    @endif

    @if($journal->status === 'approved')
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4">
        <div class="text-xs font-bold text-green-600 mb-1">Jurnal Disetujui</div>
        <p class="text-sm text-green-800">
            Diverifikasi oleh {{ $journal->reviewer?->name ?? 'Koordinator' }}
            pada {{ $journal->reviewed_at?->format('d M Y') }}.
        </p>
        @if($journal->reviewer_notes)
        <p class="text-sm text-green-700 mt-1">{{ $journal->reviewer_notes }}</p>
        @endif
    </div>
    @endif

    @php $item = $journal->item; @endphp

    @if($item)
    {{-- Akademik --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4">
        <h2 class="font-bold text-sc-ink-900 mb-4">Akademik & Perkuliahan</h2>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="text-center p-3 bg-sc-teal-50 rounded-xl">
                <div class="text-xl font-bold text-sc-teal-700">{{ $item->gpa_current_semester ?? '—' }}</div>
                <div class="text-xs text-sc-ink-500 mt-1">IPS Semester</div>
            </div>
            <div class="text-center p-3 bg-sc-teal-50 rounded-xl">
                <div class="text-xl font-bold text-sc-teal-700">{{ $item->cumulative_gpa ?? '—' }}</div>
                <div class="text-xs text-sc-ink-500 mt-1">IPK Kumulatif</div>
            </div>
            <div class="text-center p-3 bg-sc-teal-50 rounded-xl">
                <div class="text-xl font-bold text-sc-teal-700">{{ $item->class_attendance_percentage !== null ? $item->class_attendance_percentage . '%' : '—' }}</div>
                <div class="text-xs text-sc-ink-500 mt-1">Kehadiran</div>
            </div>
        </div>
        @if($item->academic_summary)
        <div>
            <div class="text-xs font-semibold text-sc-ink-500 mb-1">Ringkasan Perkuliahan</div>
            <p class="text-sm text-sc-ink-700">{{ $item->academic_summary }}</p>
        </div>
        @endif
    </div>

    {{-- Aktivitas --}}
    @if($item->organization_activities || $item->training_seminars || $item->achievements)
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4">
        <h2 class="font-bold text-sc-ink-900 mb-4">Aktivitas & Pengembangan Diri</h2>
        @foreach([
            ['label' => 'Organisasi / UKM', 'value' => $item->organization_activities],
            ['label' => 'Pelatihan / Seminar', 'value' => $item->training_seminars],
            ['label' => 'Prestasi', 'value' => $item->achievements],
        ] as $row)
        @if($row['value'])
        <div class="mb-3">
            <div class="text-xs font-semibold text-sc-ink-500 mb-1">{{ $row['label'] }}</div>
            <p class="text-sm text-sc-ink-700">{{ $row['value'] }}</p>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    {{-- Pelayanan --}}
    @if($item->community_service_details || $item->service_hours)
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4">
        <h2 class="font-bold text-sc-ink-900 mb-4">Pelayanan & Pengabdian Masyarakat</h2>
        @if($item->service_hours !== null)
        <div class="inline-flex items-center gap-2 bg-sc-orange-50 text-sc-orange-700 border border-sc-orange-200 px-3 py-1 rounded-full text-sm font-semibold mb-3">
            {{ $item->service_hours }} jam pelayanan
        </div>
        @endif
        @if($item->community_service_details)
        <p class="text-sm text-sc-ink-700">{{ $item->community_service_details }}</p>
        @endif
    </div>
    @endif

    {{-- Refleksi --}}
    @if($item->personal_reflection || $item->next_month_goals)
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4">
        <h2 class="font-bold text-sc-ink-900 mb-4">Refleksi & Rencana</h2>
        @if($item->personal_reflection)
        <div class="mb-3">
            <div class="text-xs font-semibold text-sc-ink-500 mb-1">Refleksi Rohani & Evaluasi</div>
            <p class="text-sm text-sc-ink-700">{{ $item->personal_reflection }}</p>
        </div>
        @endif
        @if($item->next_month_goals)
        <div>
            <div class="text-xs font-semibold text-sc-ink-500 mb-1">Target Bulan Depan</div>
            <p class="text-sm text-sc-ink-700">{{ $item->next_month_goals }}</p>
        </div>
        @endif
    </div>
    @endif
    @endif

    {{-- Attachments --}}
    @if($journal->attachments->count())
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4">
        <h2 class="font-bold text-sc-ink-900 mb-3">Lampiran Bukti</h2>
        <div class="space-y-2">
            @foreach($journal->attachments as $att)
            <div class="flex items-center gap-3 p-3 border border-sc-line rounded-xl">
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-sc-ink-800 truncate">{{ $att->file_name }}</div>
                    <div class="text-xs text-sc-ink-400 mt-0.5">{{ str_replace('_', ' ', ucfirst($att->file_type)) }}</div>
                </div>
                <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                   class="text-xs text-sc-teal-600 font-semibold hover:underline">Buka</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($journal->isEditable())
    <div class="mt-4">
        <a href="{{ route('scholarship-journal.edit', $journal->id) }}"
           class="block w-full text-center px-5 py-3 bg-sc-teal-600 text-white rounded-xl font-bold hover:bg-sc-teal-700">
            Edit & Kirim Jurnal
        </a>
    </div>
    @endif
</div>
@endsection
