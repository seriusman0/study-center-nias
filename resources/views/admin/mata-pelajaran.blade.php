@extends('layouts.admin')

@section('page-title', 'Master Mata Pelajaran')

@section('content')
<div class="row">
    {{-- Add Form --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h6 class="m-0">Tambah Mata Pelajaran</h6></div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-sm py-2">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-sm py-2">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('admin.mata-pelajaran.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Nama Mata Pelajaran</label>
                        <input type="text" name="nama" class="form-control form-control-sm"
                               placeholder="Contoh: FISIKA" required
                               value="{{ old('nama') }}" style="text-transform:uppercase">
                        <small class="text-muted">Akan disimpan dalam huruf kapital.</small>
                    </div>
                    <div class="form-group">
                        <label>Urutan <span class="text-muted">(opsional)</span></label>
                        <input type="number" name="urutan" class="form-control form-control-sm"
                               min="0" placeholder="0" value="{{ old('urutan', 0) }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary btn-block">Tambah</button>
                </form>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <small class="text-muted">
                    Mata pelajaran aktif akan tampil sebagai pilihan saat setting cabang dan form pendaftaran.
                    Menonaktifkan tidak menghapus data cabang yang sudah memilihnya.
                </small>
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">Daftar Mata Pelajaran</h6>
                <a href="{{ route('admin.cabangs') }}" class="btn btn-xs btn-outline-secondary">← Kembali ke Cabang</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr class="{{ $item->is_active ? '' : 'text-muted' }}">
                            <td>{{ $item->id }}</td>
                            <td><strong>{{ $item->nama }}</strong></td>
                            <td>{{ $item->urutan }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-xs btn-info"
                                        onclick="openEdit({{ $item->id }}, '{{ addslashes($item->nama) }}', {{ $item->urutan }}, {{ $item->is_active ? 'true' : 'false' }})">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.mata-pelajaran.toggle', $item->id) }}"
                                      class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs {{ $item->is_active ? 'btn-warning' : 'btn-success' }}">
                                        {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.mata-pelajaran.destroy', $item->id) }}"
                                      class="d-inline" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Edit Mata Pelajaran</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="nama" id="edit-nama" class="form-control"
                           required style="text-transform:uppercase">
                </div>
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="urutan" id="edit-urutan" class="form-control" min="0">
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="edit-is-active" name="is_active" value="1">
                    <label class="form-check-label" for="edit-is-active">Aktif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(id, nama, urutan, isActive) {
    document.getElementById('editForm').action = '/admin/mata-pelajaran/' + id;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-urutan').value = urutan;
    document.getElementById('edit-is-active').checked = isActive;
    $('#editModal').modal('show');
}
</script>
@endpush
