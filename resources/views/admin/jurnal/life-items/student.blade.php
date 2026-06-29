@extends('layouts.admin')
@section('page-title', 'Jadwal Kehidupan: ' . $student->name)

@section('content')
<a href="{{ route('admin.jurnal.life-items.index') }}" class="btn btn-sm btn-link mb-2"><i class="fas fa-arrow-left"></i> Kembali</a>

<form method="POST" action="{{ route('admin.jurnal.life-items.sync', $student) }}" id="formJurnalAssign">
    @csrf
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center">
            <div class="mr-auto">
                <strong>{{ $student->name }}</strong>
                <small class="text-muted">@ {{ $student->username }} · {{ $student->cabang?->nama ?? '—' }}</small>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAll(true)">
                    <i class="fas fa-check-double"></i> Pilih Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">
                    <i class="far fa-square"></i> Kosongkan Semua
                </button>
            </div>
        </div>
        <div class="card-body">
            @php
                $labels = ['kerohanian' => 'Kerohanian', 'pendidikan' => 'Pendidikan', 'karakter' => 'Karakter'];
            @endphp
            @foreach($labels as $key => $label)
                <div class="mb-4 jurnal-kategori" data-kategori="{{ $key }}">
                    <div class="d-flex align-items-center mb-2">
                        <h6 class="text-uppercase text-muted small font-weight-bold mb-0 mr-auto">{{ $label }}</h6>
                        <button type="button" class="btn btn-sm btn-link py-0" onclick="toggleKategori('{{ $key }}', true)">Pilih Semua</button>
                        <span class="text-muted">·</span>
                        <button type="button" class="btn btn-sm btn-link py-0 text-muted" onclick="toggleKategori('{{ $key }}', false)">Kosongkan</button>
                    </div>

                    <div class="ml-2 mb-2">
                        <div class="small text-muted mb-1">Template global (pilih yang berlaku):</div>
                        @forelse(($templates[$key] ?? collect()) as $it)
                            <div class="form-check">
                                <input type="checkbox" name="template_ids[]" value="{{ $it->id }}"
                                    id="tpl{{ $it->id }}" class="form-check-input jurnal-tpl"
                                    {{ in_array($it->id, $assignedIds) ? 'checked' : '' }}>
                                <label for="tpl{{ $it->id }}" class="form-check-label">{{ $it->label }}</label>
                            </div>
                        @empty
                            <div class="text-muted small">Belum ada template aktif.</div>
                        @endforelse
                    </div>

                    @if(($custom[$key] ?? collect())->isNotEmpty())
                        <div class="ml-2 mb-2">
                            <div class="small text-muted mb-1">Custom untuk siswa ini:</div>
                            @foreach($custom[$key] as $c)
                                <div class="d-flex align-items-center">
                                    <span class="mr-auto">• {{ $c->label }}</span>
                                    <button type="button" class="btn btn-sm btn-link text-danger py-0"
                                        onclick="if(confirm('Hapus item custom ini?')) document.getElementById('delCustom{{ $c->id }}').submit();">hapus</button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="ml-2 mt-2">
                        <div class="small text-muted mb-1">Tambah custom (kosongkan jika tidak perlu):</div>
                        <div class="input-group input-group-sm">
                            <input type="hidden" name="custom[{{ $loop->index }}][kategori]" value="{{ $key }}">
                            <input type="text" name="custom[{{ $loop->index }}][label]" class="form-control" placeholder="Label item custom...">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card-footer">
            <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            <a href="{{ route('admin.jurnal.life-items.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </div>
</form>

{{-- Hidden delete forms for custom items --}}
@foreach($labels as $key => $label)
    @foreach(($custom[$key] ?? collect()) as $c)
        <form id="delCustom{{ $c->id }}" method="POST" action="{{ route('admin.jurnal.life-items.destroy', $c) }}" class="d-none">
            @csrf @method('DELETE')
        </form>
    @endforeach
@endforeach

@push('scripts')
<script>
function toggleAll(state) {
    document.querySelectorAll('#formJurnalAssign .jurnal-tpl').forEach(cb => cb.checked = state);
}
function toggleKategori(kat, state) {
    document.querySelectorAll('.jurnal-kategori[data-kategori="'+kat+'"] .jurnal-tpl')
        .forEach(cb => cb.checked = state);
}
</script>
@endpush
@endsection
