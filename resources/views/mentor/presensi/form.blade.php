@extends('layouts.app')
@section('title', $presensi ? 'Edit Presensi' : 'Buat Presensi')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css">
<style>
    .ts-wrapper.single .ts-control { padding: 8px 12px; border-radius: 8px; }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <a href="{{ route('mentor-presensi.index') }}" class="text-sm text-sc-ink-500 hover:text-sc-teal-700">&larr; Kembali</a>
    <h1 class="text-xl font-bold text-sc-ink-900 mt-2 mb-4">{{ $presensi ? 'Edit Presensi' : 'Buat Presensi Mentor' }}</h1>

    @if(!$cabangId)
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            Akun Anda belum terkait cabang. Hubungi admin.
        </div>
    @endif

    <form method="POST" action="{{ $presensi ? route('mentor-presensi.update', $presensi) : route('mentor-presensi.store') }}"
          class="bg-white shadow-sc-1 border border-sc-line rounded-xl p-5 space-y-4">
        @csrf
        @if($presensi) @method('PUT') @endif

        <div>
            <label class="block text-sm font-semibold mb-1">Nama Kelas <span class="text-red-500">*</span></label>
            <select id="kelasPicker" name="kelas_id" required></select>
            <p class="text-xs text-gray-500 mt-1">Pilih dari master kelas cabang Anda.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-semibold mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" required
                    max="{{ now('Asia/Jakarta')->toDateString() }}"
                    value="{{ old('tanggal', $presensi?->tanggal?->toDateString() ?? now('Asia/Jakarta')->toDateString()) }}"
                    class="w-full border border-sc-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Jam Datang <span class="text-red-500">*</span></label>
                <input type="time" name="jam_datang" required
                    value="{{ old('jam_datang', $presensi ? substr($presensi->jam_datang, 0, 5) : '') }}"
                    class="w-full border border-sc-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Jam Pulang <span class="text-red-500">*</span></label>
                <input type="time" name="jam_pulang" required
                    value="{{ old('jam_pulang', $presensi ? substr($presensi->jam_pulang, 0, 5) : '') }}"
                    class="w-full border border-sc-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Jumlah Murid <span class="text-red-500">*</span></label>
            <input type="number" name="jumlah_murid" min="0" max="500" required
                value="{{ old('jumlah_murid', $presensi?->jumlah_murid ?? 0) }}"
                class="w-full border border-sc-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Catatan <span class="text-gray-400 text-xs font-normal">(opsional)</span></label>
            <textarea name="catatan" rows="3" maxlength="1000"
                class="w-full border border-sc-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">{{ old('catatan', $presensi?->catatan) }}</textarea>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button type="submit" {{ !$cabangId ? 'disabled' : '' }}
                class="inline-flex items-center gap-1.5 px-5 py-2 bg-sc-teal-600 hover:bg-sc-teal-700 text-white rounded-lg font-semibold transition disabled:opacity-50">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ $presensi ? 'Simpan Perubahan' : 'Simpan Presensi' }}
            </button>
            <a href="{{ route('mentor-presensi.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sc-ink-700 hover:bg-sc-line-soft rounded-lg transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function() {
    const searchUrl = @json(route('mentor-presensi.kelas.search'));
    const initial = @json($presensi?->kelas ? ['id' => $presensi->kelas->id, 'nama' => $presensi->kelas->nama, 'label' => $presensi->kelas->nama] : null);

    const ts = new TomSelect('#kelasPicker', {
        valueField: 'id',
        labelField: 'label',
        searchField: ['nama', 'label'],
        maxOptions: 50,
        placeholder: 'Pilih atau cari kelas...',
        load: function(query, callback) {
            const params = new URLSearchParams();
            if (query) params.set('q', query);
            fetch(searchUrl + '?' + params.toString())
                .then(r => r.json())
                .then(json => callback(json.data || []))
                .catch(() => callback([]));
        },
        render: {
            option: function(item, escape) {
                return `<div><strong>${escape(item.nama)}</strong></div>`;
            },
        },
    });
    if (initial) {
        ts.addOption(initial);
        ts.addItem(String(initial.id), true);
    }
})();
</script>
@endpush
@endsection
