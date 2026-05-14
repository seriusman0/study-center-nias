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
    <a href="{{ route('mentor-presensi.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Kembali</a>
    <h1 class="text-xl font-bold text-[#1e3a5f] mt-2 mb-4">{{ $presensi ? 'Edit Presensi' : 'Buat Presensi Mentor' }}</h1>

    @if(!$cabangId)
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded text-sm text-red-800">
            Akun Anda belum terkait cabang. Hubungi admin.
        </div>
    @endif

    <form method="POST" action="{{ $presensi ? route('mentor-presensi.update', $presensi) : route('mentor-presensi.store') }}"
          class="bg-white shadow rounded-xl p-5 space-y-4">
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
                    class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Jam Datang <span class="text-red-500">*</span></label>
                <input type="time" name="jam_datang" required
                    value="{{ old('jam_datang', $presensi ? substr($presensi->jam_datang, 0, 5) : '') }}"
                    class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Jam Pulang <span class="text-red-500">*</span></label>
                <input type="time" name="jam_pulang" required
                    value="{{ old('jam_pulang', $presensi ? substr($presensi->jam_pulang, 0, 5) : '') }}"
                    class="w-full border rounded px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Jumlah Murid <span class="text-red-500">*</span></label>
            <input type="number" name="jumlah_murid" min="0" max="500" required
                value="{{ old('jumlah_murid', $presensi?->jumlah_murid ?? 0) }}"
                class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Catatan <span class="text-gray-400 text-xs font-normal">(opsional)</span></label>
            <textarea name="catatan" rows="3" maxlength="1000"
                class="w-full border rounded px-3 py-2 text-sm">{{ old('catatan', $presensi?->catatan) }}</textarea>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button type="submit" {{ !$cabangId ? 'disabled' : '' }}
                class="px-5 py-2 bg-[#1e3a5f] text-white rounded font-semibold disabled:opacity-50">
                {{ $presensi ? 'Simpan Perubahan' : 'Simpan Presensi' }}
            </button>
            <a href="{{ route('mentor-presensi.index') }}" class="px-4 py-2 text-gray-600 hover:underline">Batal</a>
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
