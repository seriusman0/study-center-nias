@extends('layouts.admin')

@section('page-title', 'Permission')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Tambah Permission</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.permissions.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Nama Permission <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required pattern="[a-z0-9_\.]+">
                        <small class="text-muted">contoh: <code>manage_users</code>, <code>blog.publish</code></small>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Daftar Permission</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Nama</th><th>Deskripsi</th><th>Role</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $p)
                        <tr>
                            <td><strong>{{ $p->name }}</strong></td>
                            <td style="font-size:13px">{{ $p->description ?? '-' }}</td>
                            <td>{{ $p->roles_count }}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#editPerm{{ $p->id }}">Edit</button>
                                <form method="POST" action="{{ route('admin.permissions.delete', $p->id) }}" class="d-inline" onsubmit="return confirm('Hapus permission ini?')">
                                    @csrf @method('DELETE')
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

@foreach($permissions as $p)
<div class="modal fade" id="editPerm{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.permissions.update', $p->id) }}" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h6 class="modal-title">Edit Permission: {{ $p->name }}</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ $p->name }}" required pattern="[a-z0-9_\.]+">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <input type="text" name="description" class="form-control" value="{{ $p->description }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
