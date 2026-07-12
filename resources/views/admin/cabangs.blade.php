@extends('layouts.admin')

@section('page-title', 'Cabang')

@section('content')
<div class="row">
    {{-- Add Form --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h6 class="m-0">Tambah Cabang</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.cabangs.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Nama Cabang</label>
                        <input type="text" name="nama" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" name="alamat" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label>Kontak</label>
                        <input type="text" name="kontak" class="form-control form-control-sm">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="foto_wajib_add" name="foto_wajib" value="1" checked>
                        <label class="form-check-label" for="foto_wajib_add">Foto wajib saat pendaftaran</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary btn-block">Tambah</button>
                </form>
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h6 class="m-0">Daftar Cabang</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th>Alamat</th>
                            <th>Kontak</th>
                            <th>Foto</th>
                            <th>Blog</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cabangs as $cabang)
                        <tr>
                            <td>{{ $cabang->nama }}</td>
                            <td class="text-muted" style="font-size:12px">{{ $cabang->slug }}</td>
                            <td style="font-size:13px">{{ $cabang->alamat ?? '-' }}</td>
                            <td style="font-size:13px">{{ $cabang->kontak ?? '-' }}</td>
                            <td>
                                @if($cabang->foto_wajib)
                                    <span class="badge badge-danger">Wajib</span>
                                @else
                                    <span class="badge badge-secondary">Opsional</span>
                                @endif
                            </td>
                            <td>{{ $cabang->blogs_count }}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-info"
                                        onclick="openEdit({{ $cabang->id }}, '{{ addslashes($cabang->nama) }}', '{{ addslashes($cabang->alamat) }}', '{{ addslashes($cabang->kontak) }}', {{ $cabang->foto_wajib ? 'true' : 'false' }})">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.cabangs.delete', $cabang->id) }}"
                                      class="d-inline" onsubmit="return confirm('Hapus cabang ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
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
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Cabang</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="nama" id="edit-nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="alamat" id="edit-alamat" class="form-control">
                </div>
                <div class="form-group">
                    <label>Kontak</label>
                    <input type="text" name="kontak" id="edit-kontak" class="form-control">
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="edit-foto-wajib" name="foto_wajib" value="1">
                    <label class="form-check-label" for="edit-foto-wajib">Foto wajib saat pendaftaran</label>
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
function openEdit(id, nama, alamat, kontak, fotoWajib) {
    document.getElementById('editForm').action = '/admin/cabangs/' + id;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-alamat').value = alamat;
    document.getElementById('edit-kontak').value = kontak;
    document.getElementById('edit-foto-wajib').checked = fotoWajib;
    $('#editModal').modal('show');
}
</script>
@endpush
