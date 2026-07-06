@extends('layouts.app')

@section('title', 'Dashboard Koordinator')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

    <div class="bg-gradient-to-br from-sc-teal-700 to-sc-teal-600 text-white shadow-sc-3 rounded-2xl p-5 mb-6">
        <h1 class="font-display text-2xl">Dashboard Koordinator</h1>
        @if(auth()->user()->collegeProfile)
        <p class="text-sm text-white/80 mt-1">{{ auth()->user()->collegeProfile->institution_name }}</p>
        @endif
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Kampus</label>
            <select name="campus" class="border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500">
                <option value="">Semua Kampus</option>
                @foreach($campuses as $campus)
                <option value="{{ $campus }}" {{ request('campus') === $campus ? 'selected' : '' }}>{{ $campus }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Status</label>
            <select name="status" class="border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500">
                <option value="">Semua Status</option>
                <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Review</option>
                <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Sedang Ditinjau</option>
                <option value="revision_required" {{ request('status') === 'revision_required' ? 'selected' : '' }}>Perlu Revisi</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-sc-teal-600 text-white rounded-xl text-sm font-bold hover:bg-sc-teal-700">
            Filter
        </button>
        @if(request()->hasAny(['campus', 'status']))
        <a href="{{ route('college.dashboard') }}" class="px-4 py-2 border border-sc-line rounded-xl text-sm text-sc-ink-500 hover:bg-sc-ink-50">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-sc-line flex items-center justify-between">
            <h2 class="font-bold text-sc-ink-900">Daftar Jurnal Mahasiswa</h2>
            <span class="text-xs text-sc-ink-400">{{ $journals->total() }} jurnal</span>
        </div>

        @if($journals->isEmpty())
        <div class="px-5 py-10 text-center text-sc-ink-400 text-sm">Tidak ada jurnal ditemukan.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-sc-ink-50 text-xs text-sc-ink-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">Mahasiswa</th>
                        <th class="px-4 py-3 text-left">Kampus</th>
                        <th class="px-4 py-3 text-left">Periode</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Dikirim</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sc-line">
                    @foreach($journals as $journal)
                    @php
                    $statusColor = match($journal->status) {
                        'approved' => 'bg-green-100 text-green-700',
                        'submitted', 'under_review' => 'bg-yellow-100 text-yellow-700',
                        'revision_required' => 'bg-red-100 text-red-700',
                        default => 'bg-sc-ink-100 text-sc-ink-500',
                    };
                    @endphp
                    <tr class="hover:bg-sc-ink-50">
                        <td class="px-4 py-3 font-semibold text-sc-ink-800">{{ $journal->student->name }}</td>
                        <td class="px-4 py-3 text-sc-ink-500">{{ $journal->student->studentProfile?->campus_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sc-ink-600">
                            {{ \Carbon\Carbon::createFromDate(null, $journal->period_month, 1)->locale('id')->isoFormat('MMMM') }}
                            {{ $journal->period_year }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $statusColor }}">
                                {{ $journal->statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sc-ink-400 text-xs">
                            {{ $journal->submitted_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('college.journal.show', $journal->id) }}"
                               class="text-xs text-sc-teal-600 font-semibold hover:underline">
                                {{ in_array($journal->status, ['submitted', 'under_review']) ? 'Review' : 'Lihat' }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-sc-line">
            {{ $journals->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
