@extends('layouts.admin')
@section('page-title', 'Master Kelas')

@section('content')
<div class="row">
    @if(auth()->user()->isAdmin())
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h6 class="m-0">Tambah Kelas</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.kelas-master.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Nama Kelas</label>
                        <input type="text" name="nama" class="form-control form-control-sm" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Cabang</label>
                        <select name="cabang_id" class="form-control form-control-sm" required>
                            <option value="">- Pilih cabang -</option>
                            @foreach($cabangs as $c)
                                <option value="{{ $c->id }}">{{ $c->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" class="form-control form-control-sm" maxlength="255">
                    </div>
                    <div class="form-check mb-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" id="actAdd" class="form-check-input" checked>
                        <label for="actAdd" class="form-check-label">Aktif</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary btn-block">Tambah</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <div class="{{ auth()->user()->isAdmin() ? 'col-md-8' : 'col-12' }}">
        <div class="card">
            <div class="card-header">
                <form method="GET" class="form-inline">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Cari nama kelas">
                    @if($cabangs->count() > 1)
                        <select name="cabang_id" class="form-control form-control-sm mr-2">
                            <option value="">Semua cabang</option>
                            @foreach($cabangs as $c)
                                <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button class="btn btn-sm btn-outline-primary">Filter</button>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama</th>
                            <th>Cabang</th>
                            <th>Keterangan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Sesi Mentor</th>
                            <th class="text-center">Presensi</th>
                            @if(auth()->user()->isAdmin())<th class="text-right">Aksi</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelas as $k)
                        <tr>
                            <td><strong>{{ $k->nama }}</strong></td>
                            <td><small>{{ $k->cabang?->nama ?? '—' }}</small></td>
                            <td><small class="text-muted">{{ $k->keterangan ?: '—' }}</small></td>
                            <td class="text-center">
                                @if($k->is_active)
                                    <span class="badge badge-success">aktif</span>
                                @else
                                    <span class="badge badge-secondary">non-aktif</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $k->mentor_presensi_count }}</td>
                            <td class="text-center">{{ $k->presensi_count }}</td>
                            @if(auth()->user()->isAdmin())
                            <td class="text-right">
                                <button class="btn btn-xs btn-info"
                                    onclick="openEdit({{ $k->id }}, @js($k->nama), {{ $k->cabang_id }}, @js($k->keterangan), {{ $k->is_active ? 'true' : 'false' }})">Edit</button>
                                <form method="POST" action="{{ route('admin.kelas-master.destroy', $k) }}" class="d-inline"
                                    onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger">Hapus</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada kelas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $kelas->links() }}</div>
        </div>
    </div>
</div>

@if(auth()->user()->isAdmin())
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editForm" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Edit Kelas</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group"><label>Nama</label>
                    <input type="text" name="nama" id="edit-nama" class="form-control" required></div>
                <div class="form-group"><label>Cabang</label>
                    <select name="cabang_id" id="edit-cabang" class="form-control" required>
                        @foreach($cabangs as $c)<option value="{{ $c->id }}">{{ $c->nama }}</option>@endforeach
                    </select></div>
                <div class="form-group"><label>Keterangan</label>
                    <input type="text" name="keterangan" id="edit-keterangan" class="form-control"></div>
                <div class="form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="edit-aktif" class="form-check-input">
                    <label for="edit-aktif" class="form-check-label">Aktif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function openEdit(id, nama, cabangId, ket, aktif) {
    document.getElementById('editForm').action = '/admin/kelas-master/' + id;
    document.getElementById('edit-nama').value = nama || '';
    document.getElementById('edit-cabang').value = cabangId || '';
    document.getElementById('edit-keterangan').value = ket || '';
    document.getElementById('edit-aktif').checked = !!aktif;
    $('#editModal').modal('show');
}
</script>
@endpush
