@extends('layouts.app')
@section('title', 'Kelas Master - Study Center Nias')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-sc-ink-900">Kelas Master</h1>
            <p class="text-xs text-sc-ink-400 mt-0.5">{{ auth()->user()->cabang?->nama ?? 'Cabang Anda' }}</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="px-4 py-2 bg-sc-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-sc-teal-700 transition">
            + Tambah
        </button>
    </div>

    {{-- Search --}}
    <form method="GET" class="flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Cari nama kelas..."
               class="flex-1 rounded-xl border border-sc-line px-4 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
        <button type="submit"
                class="px-4 py-2 bg-sc-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-sc-teal-700 transition">
            Cari
        </button>
    </form>

    {{-- List --}}
    <div class="bg-white rounded-2xl border border-sc-line shadow-sc-2 overflow-hidden">
        @forelse($kelas as $k)
        <div class="flex items-center gap-3 px-4 py-3 border-b border-sc-line last:border-0">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-sc-ink-900">{{ $k->nama }}</p>
                <p class="text-xs text-sc-ink-400">{{ $k->cabang?->nama ?? '—' }} · {{ $k->keterangan ?: '—' }}</p>
                <p class="text-xs text-sc-ink-300 mt-0.5">
                    Sesi: {{ $k->mentor_presensi_count }} · Presensi: {{ $k->presensi_count }}
                    @if($k->is_active)
                        <span class="ml-1 text-sc-teal-600 font-medium">aktif</span>
                    @else
                        <span class="ml-1 text-gray-400">non-aktif</span>
                    @endif
                </p>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <button onclick="openEdit({{ $k->id }}, @js($k->nama), @js($k->keterangan), {{ $k->is_active ? 'true' : 'false' }})"
                        class="px-3 py-1.5 text-xs font-semibold text-sc-teal-700 border border-sc-teal-300 rounded-lg hover:bg-sc-teal-50 transition">
                    Edit
                </button>
                <form method="POST" action="{{ route('mentor.kelas-master.destroy', $k) }}"
                      onsubmit="return confirm('Hapus kelas ini?')" class="inline">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 text-xs font-semibold text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-4 py-10 text-center text-sm text-sc-ink-400">
            Belum ada kelas.
        </div>
        @endforelse
    </div>

    <div>{{ $kelas->withQueryString()->links() }}</div>

</div>

{{-- Add Modal --}}
<div id="addModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-end sm:items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
        <form method="POST" action="{{ route('mentor.kelas-master.store') }}">
            @csrf
            <div class="px-5 py-4 border-b border-sc-line flex items-center justify-between">
                <h2 class="text-base font-bold text-sc-ink-900">Tambah Kelas</h2>
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400">✕</button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <div>
                    <label class="text-xs font-semibold text-sc-ink-600 block mb-1">Nama Kelas</label>
                    <input type="text" name="nama" required maxlength="100"
                           class="w-full rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
                </div>
                <input type="hidden" name="cabang_id" value="{{ auth()->user()->cabang_id }}">
                <div>
                    <label class="text-xs font-semibold text-sc-ink-600 block mb-1">Keterangan</label>
                    <input type="text" name="keterangan" maxlength="255"
                           class="w-full rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded">
                    Aktif
                </label>
            </div>
            <div class="px-5 py-4 border-t border-sc-line flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-sc-ink-600 border border-sc-line rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold text-white bg-sc-teal-600 rounded-xl hover:bg-sc-teal-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-end sm:items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="px-5 py-4 border-b border-sc-line flex items-center justify-between">
                <h2 class="text-base font-bold text-sc-ink-900">Edit Kelas</h2>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400">✕</button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <div>
                    <label class="text-xs font-semibold text-sc-ink-600 block mb-1">Nama Kelas</label>
                    <input type="text" name="nama" id="edit-nama" required maxlength="100"
                           class="w-full rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-sc-ink-600 block mb-1">Keterangan</label>
                    <input type="text" name="keterangan" id="edit-keterangan" maxlength="255"
                           class="w-full rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="edit-aktif" class="rounded">
                    Aktif
                </label>
            </div>
            <div class="px-5 py-4 border-t border-sc-line flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-sc-ink-600 border border-sc-line rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold text-white bg-sc-teal-600 rounded-xl hover:bg-sc-teal-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEdit(id, nama, ket, aktif) {
    document.getElementById('editForm').action = '/mentor/kelas-master/' + id;
    document.getElementById('edit-nama').value = nama || '';
    document.getElementById('edit-keterangan').value = ket || '';
    document.getElementById('edit-aktif').checked = !!aktif;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endpush
