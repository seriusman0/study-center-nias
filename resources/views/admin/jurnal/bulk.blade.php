@extends('layouts.admin')
@section('page-title', 'Input Massal Jurnal')

@section('content')
<a href="{{ route('admin.jurnal.reports.index') }}" class="btn btn-sm btn-link mb-3"><i class="fas fa-arrow-left"></i> Kembali ke Laporan</a>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

<form method="GET" action="{{ route('admin.jurnal.bulk.create') }}" class="card mb-3">
    <div class="card-header font-weight-bold"><i class="fas fa-filter mr-1"></i> Filter Siswa</div>
    <div class="card-body">
        <div class="form-row align-items-end">
            <div class="col-auto">
                <label class="small font-weight-bold">Tanggal Jurnal</label>
                <input type="date" name="date" value="{{ $date }}" max="{{ \App\Support\JurnalWeek::today()->toDateString() }}"
                    class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <label class="small font-weight-bold">Cabang</label>
                <select name="cabang_id" class="form-control form-control-sm">
                    <option value="">Semua cabang</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Tampilkan</button>
            </div>
        </div>
    </div>
</form>

<form method="POST" action="{{ route('admin.jurnal.bulk.store') }}">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row">
        {{-- Kolom kiri: item yang akan diisi --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header font-weight-bold"><i class="fas fa-check-square mr-1"></i> Item yang Diisi</div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Centang item yang akan diterapkan ke semua siswa yang dipilih.
                        Item yang sudah terisi tidak akan di-overwrite.
                    </p>

                    <div class="mb-3">
                        <div class="font-weight-bold small text-uppercase text-muted mb-1">Pembacaan Alkitab</div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="item_pl" name="items[pl]" value="1">
                            <label class="custom-control-label" for="item_pl">Perjanjian Lama (PL)</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="item_pb" name="items[pb]" value="1">
                            <label class="custom-control-label" for="item_pb">Perjanjian Baru (PB)</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="font-weight-bold small text-uppercase text-muted mb-1">Hafal Ayat</div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="item_verse" name="items[verse]" value="1">
                            <label class="custom-control-label" for="item_verse">Hafal Ayat (minggu ini)</label>
                        </div>
                    </div>

                    @if($defaultItems->isNotEmpty())
                    @foreach($defaultItems->groupBy('kategori') as $kategori => $items)
                    <div class="mb-3">
                        <div class="font-weight-bold small text-uppercase text-muted mb-1">{{ ucfirst($kategori) }}</div>
                        @foreach($items as $item)
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input"
                                id="life_{{ $item->id }}" name="items[life][]" value="{{ $item->id }}">
                            <label class="custom-control-label" for="life_{{ $item->id }}">{{ $item->label }}</label>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted small">Tidak ada item default aktif.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom kanan: pilih siswa --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <span class="font-weight-bold"><i class="fas fa-users mr-1"></i> Pilih Siswa</span>
                    <small class="ml-auto text-muted">{{ $date }} &bull; {{ $students->count() }} siswa</small>
                </div>
                <div class="card-body p-2">
                    @if($students->isEmpty())
                        <p class="text-center text-muted py-3">Tidak ada siswa ditemukan.</p>
                    @else
                        <div class="px-2 py-1 border-bottom mb-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="select_all">
                                <label class="custom-control-label font-weight-bold" for="select_all">Pilih Semua ({{ $students->count() }})</label>
                            </div>
                        </div>
                        <div style="max-height:400px;overflow-y:auto;">
                        @foreach($students as $s)
                        <div class="custom-control custom-checkbox px-2 py-1 border-bottom student-row">
                            <input type="checkbox" class="custom-control-input student-check"
                                id="student_{{ $s->id }}" name="student_ids[]" value="{{ $s->id }}">
                            <label class="custom-control-label w-100" for="student_{{ $s->id }}">
                                <span class="font-weight-bold">{{ $s->name }}</span>
                                <small class="text-muted ml-1">@{{ $s->username }}</small>
                                @if($s->cabang)
                                    <span class="badge badge-light float-right">{{ $s->cabang->nama }}</span>
                                @endif
                            </label>
                        </div>
                        @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 text-right">
        <button type="submit" class="btn btn-primary" onclick="return confirmSubmit(this)">
            <i class="fas fa-save mr-1"></i> Simpan Jurnal Massal
        </button>
    </div>
</form>

@push('scripts')
<script>
document.getElementById('select_all')?.addEventListener('change', function() {
    document.querySelectorAll('.student-check').forEach(c => c.checked = this.checked);
});

document.querySelectorAll('.student-check').forEach(c => {
    c.addEventListener('change', function() {
        const all = document.querySelectorAll('.student-check');
        const checked = document.querySelectorAll('.student-check:checked');
        document.getElementById('select_all').checked = all.length === checked.length;
        document.getElementById('select_all').indeterminate = checked.length > 0 && checked.length < all.length;
    });
});

function confirmSubmit(btn) {
    const n = document.querySelectorAll('.student-check:checked').length;
    if (n === 0) { alert('Pilih minimal 1 siswa.'); return false; }
    const items = document.querySelectorAll('input[name^="items"]:checked').length;
    if (items === 0) { alert('Pilih minimal 1 item yang akan diisi.'); return false; }
    return confirm(`Simpan jurnal untuk ${n} siswa pada tanggal {{ $date }}?`);
}
</script>
@endpush
@endsection
