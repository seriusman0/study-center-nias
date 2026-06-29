@extends('layouts.admin')

@section('page-title', 'Generator Name Tag')

@section('content')
<form method="POST" action="{{ route('admin.nametags.generate') }}" target="_blank">
    @csrf

    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0">Pengaturan Ukuran &amp; Filter</h6>
        </div>
        <div class="card-body">
            <div class="form-row align-items-end">
                <div class="form-group col-md-2">
                    <label class="small">Lebar (cm)</label>
                    <input type="number" step="0.1" name="width_cm" value="8.5" min="5" max="15" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-2">
                    <label class="small">Tinggi (cm)</label>
                    <input type="number" step="0.1" name="height_cm" value="5.5" min="3" max="15" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-2">
                    <label class="small">Preset Ukuran</label>
                    <select class="form-control form-control-sm" id="preset">
                        <option value="8.5,5.5" selected>Default 8.5 x 5.5 cm</option>
                        <option value="9,5.5">9 x 5.5 cm</option>
                        <option value="10,6">10 x 6 cm</option>
                        <option value="8.5,5">8.5 x 5 cm</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <div class="custom-control custom-switch mt-3">
                        <input type="checkbox" name="auto_print" value="1" id="autoPrint"
                               class="custom-control-input" checked>
                        <label class="custom-control-label" for="autoPrint">Buka dialog cetak otomatis</label>
                    </div>
                </div>
                <div class="form-group col-md-3 text-right">
                    <button type="submit" class="btn btn-primary" id="genBtn" disabled>
                        <i class="fas fa-print mr-1"></i> Generate Name Tag
                        (<span id="selCount">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <form method="GET" action="{{ route('admin.nametags') }}" class="form-inline" onsubmit="event.stopPropagation();">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Cari nama/sekolah/kelas"
                       class="form-control form-control-sm mr-2" style="width:240px">
                <select name="cabang_id" class="form-control form-control-sm mr-2">
                    <option value="">Semua Cabang</option>
                    @foreach($cabangs as $c)
                    <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
            </form>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="selAll">Pilih semua di halaman</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="selClear">Bersihkan</button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:40px"></th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Sekolah</th>
                        <th>Kelas</th>
                        <th>Cabang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $s)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="user_ids[]" value="{{ $s->id }}" class="user-cb">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $s->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($s->name).'&size=28&background=1e3a5f&color=fff' }}"
                                     class="img-circle mr-2" style="width:24px;height:24px;object-fit:cover" alt="">
                                {{ $s->name }}
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:13px">{{ $s->username }}</td>
                        <td style="font-size:13px">{{ $s->studentProfile?->school_name ?? '-' }}</td>
                        <td style="font-size:13px">{{ $s->studentProfile?->grade_class ?? '-' }}</td>
                        <td style="font-size:13px">{{ $s->cabang?->nama ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada siswa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->lastPage() > 1)
        <div class="card-footer">
            {{ $students->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</form>

@push('scripts')
<script>
(function() {
    var cbs = document.querySelectorAll('.user-cb');
    var sel = document.getElementById('selCount');
    var btn = document.getElementById('genBtn');
    function refresh() {
        var n = document.querySelectorAll('.user-cb:checked').length;
        sel.textContent = n;
        btn.disabled = n === 0;
    }
    cbs.forEach(function(cb){ cb.addEventListener('change', refresh); });
    document.getElementById('selAll').addEventListener('click', function() {
        cbs.forEach(function(cb){ cb.checked = true; });
        refresh();
    });
    document.getElementById('selClear').addEventListener('click', function() {
        cbs.forEach(function(cb){ cb.checked = false; });
        refresh();
    });
    document.getElementById('preset').addEventListener('change', function(e) {
        var parts = e.target.value.split(',');
        document.querySelector('input[name=width_cm]').value  = parts[0];
        document.querySelector('input[name=height_cm]').value = parts[1];
    });
    refresh();
})();
</script>
@endpush
@endsection
