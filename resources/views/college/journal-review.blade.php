@extends('layouts.app')

@section('title', 'Review Jurnal')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

    <div class="mb-5 flex items-center gap-3">
        <a href="{{ route('college.dashboard') }}" class="text-sc-ink-400 hover:text-sc-ink-700 text-sm">&larr; Kembali</a>
        <h1 class="font-display text-xl text-sc-ink-900 flex-1">Review Jurnal</h1>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Left: Journal content --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Student info --}}
            <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <div class="font-bold text-sc-ink-900">{{ $journal->student->name }}</div>
                        <div class="text-sm text-sc-ink-500">
                            {{ $journal->student->studentProfile?->campus_name ?? '—' }}
                            @if($journal->student->studentProfile?->current_semester)
                            · Semester {{ $journal->student->studentProfile->current_semester }}
                            @endif
                        </div>
                    </div>
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
                <div class="text-xs text-sc-ink-400">{{ $journal->title }} · Dikirim: {{ $journal->submitted_at?->format('d M Y H:i') ?? '—' }}</div>
            </div>

            @php $item = $journal->item; @endphp

            @if($item)
            {{-- Akademik --}}
            <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5">
                <h3 class="font-bold text-sc-ink-800 mb-3 text-sm uppercase tracking-wide text-sc-teal-700">Akademik</h3>
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div class="text-center p-2 bg-sc-teal-50 rounded-xl">
                        <div class="font-bold text-sc-teal-700">{{ $item->gpa_current_semester ?? '—' }}</div>
                        <div class="text-xs text-sc-ink-400">IPS</div>
                    </div>
                    <div class="text-center p-2 bg-sc-teal-50 rounded-xl">
                        <div class="font-bold text-sc-teal-700">{{ $item->cumulative_gpa ?? '—' }}</div>
                        <div class="text-xs text-sc-ink-400">IPK</div>
                    </div>
                    <div class="text-center p-2 bg-sc-teal-50 rounded-xl">
                        <div class="font-bold text-sc-teal-700">{{ $item->class_attendance_percentage !== null ? $item->class_attendance_percentage . '%' : '—' }}</div>
                        <div class="text-xs text-sc-ink-400">Kehadiran</div>
                    </div>
                </div>
                @if($item->academic_summary)
                <p class="text-sm text-sc-ink-700">{{ $item->academic_summary }}</p>
                @endif
            </div>

            {{-- Aktivitas --}}
            @if($item->organization_activities || $item->training_seminars || $item->achievements)
            <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5">
                <h3 class="font-bold text-sm uppercase tracking-wide text-sc-teal-700 mb-3">Aktivitas</h3>
                @foreach([
                    ['Organisasi / UKM', $item->organization_activities],
                    ['Pelatihan / Seminar', $item->training_seminars],
                    ['Prestasi', $item->achievements],
                ] as [$label, $val])
                @if($val)
                <div class="mb-3">
                    <div class="text-xs font-semibold text-sc-ink-500 mb-1">{{ $label }}</div>
                    <p class="text-sm text-sc-ink-700">{{ $val }}</p>
                </div>
                @endif
                @endforeach
            </div>
            @endif

            {{-- Pelayanan --}}
            @if($item->community_service_details || $item->service_hours !== null)
            <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5">
                <h3 class="font-bold text-sm uppercase tracking-wide text-sc-teal-700 mb-3">Pelayanan</h3>
                @if($item->service_hours !== null)
                <div class="text-sm font-semibold text-sc-orange-600 mb-2">{{ $item->service_hours }} jam pelayanan</div>
                @endif
                @if($item->community_service_details)
                <p class="text-sm text-sc-ink-700">{{ $item->community_service_details }}</p>
                @endif
            </div>
            @endif

            {{-- Refleksi --}}
            @if($item->personal_reflection || $item->next_month_goals)
            <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5">
                <h3 class="font-bold text-sm uppercase tracking-wide text-sc-teal-700 mb-3">Refleksi & Rencana</h3>
                @if($item->personal_reflection)
                <div class="mb-3">
                    <div class="text-xs font-semibold text-sc-ink-500 mb-1">Refleksi Rohani</div>
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
            <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5">
                <h3 class="font-bold text-sm uppercase tracking-wide text-sc-teal-700 mb-3">Lampiran</h3>
                <div class="space-y-2">
                    @foreach($journal->attachments as $att)
                    <div class="flex items-center gap-3 p-2 border border-sc-line rounded-xl">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-sc-ink-800 truncate">{{ $att->file_name }}</div>
                            <div class="text-xs text-sc-ink-400">{{ str_replace('_', ' ', ucfirst($att->file_type)) }}</div>
                        </div>
                        <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                           class="text-xs text-sc-teal-600 font-semibold">Buka</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Verify panel --}}
        <div class="lg:col-span-1">
            <div class="sticky top-4">
                @if(in_array($journal->status, ['submitted', 'under_review']))
                <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5">
                    <h3 class="font-bold text-sc-ink-900 mb-4">Verifikasi Jurnal</h3>
                    <form method="POST" action="{{ route('college.journal.review', $journal->id) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Catatan / Feedback (opsional)</label>
                            <textarea name="reviewer_notes" rows="4" placeholder="Berikan catatan evaluasi kepada mahasiswa..."
                                      class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500 resize-none"></textarea>
                        </div>
                        <div class="space-y-2">
                            <button type="submit" name="action" value="approved"
                                    class="w-full px-4 py-3 bg-green-600 text-white rounded-xl font-bold text-sm hover:bg-green-700 transition">
                                ✓ Setujui Jurnal
                            </button>
                            <button type="submit" name="action" value="revision_required"
                                    class="w-full px-4 py-3 border-2 border-red-300 text-red-600 rounded-xl font-bold text-sm hover:bg-red-50 transition">
                                ↩ Minta Revisi
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5">
                    <h3 class="font-bold text-sc-ink-900 mb-2">Hasil Verifikasi</h3>
                    <div class="text-sm text-sc-ink-600">
                        Status: <strong>{{ $journal->statusLabel }}</strong>
                    </div>
                    @if($journal->reviewer_notes)
                    <div class="mt-3 p-3 bg-sc-ink-50 rounded-xl text-sm text-sc-ink-700">
                        {{ $journal->reviewer_notes }}
                    </div>
                    @endif
                    @if($journal->reviewed_at)
                    <div class="mt-2 text-xs text-sc-ink-400">
                        {{ $journal->reviewed_at->format('d M Y H:i') }}
                        @if($journal->reviewer) · {{ $journal->reviewer->name }} @endif
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
