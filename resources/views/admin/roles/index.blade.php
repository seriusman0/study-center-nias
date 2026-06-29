@extends('layouts.admin')

@section('page-title', 'Role')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Tambah Role</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Nama Role <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required pattern="[a-z0-9_]+">
                        <small class="text-muted">huruf kecil, angka, underscore</small>
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
            <div class="card-header"><h6 class="mb-0">Daftar Role</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama</th><th>Deskripsi</th><th>Pengguna</th><th>Permission</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td><strong>{{ $role->name }}</strong></td>
                            <td style="font-size:13px">{{ $role->description ?? '-' }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td>
                                @forelse($role->permissions as $p)
                                    <span class="badge badge-secondary">{{ $p->name }}</span>
                                @empty
                                    <span class="text-muted small">-</span>
                                @endforelse
                            </td>
                            <td>
                                <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#editRole{{ $role->id }}">Edit</button>
                                <button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#permRole{{ $role->id }}">Perm</button>
                                <form method="POST" action="{{ route('admin.roles.delete', $role->id) }}" class="d-inline" onsubmit="return confirm('Hapus role ini?')">
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

@foreach($roles as $role)
<div class="modal fade" id="editRole{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.roles.update', $role->id) }}" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h6 class="modal-title">Edit Role: {{ $role->name }}</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ $role->name }}" required pattern="[a-z0-9_]+">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <input type="text" name="description" class="form-control" value="{{ $role->description }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="permRole{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('admin.roles.permissions', $role->id) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">Permission untuk: {{ $role->name }}</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                @php $rolePermIds = $role->permissions->pluck('id')->all(); @endphp
                <div class="row">
                    @foreach($permissions as $p)
                    <div class="col-md-6 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}"
                                   id="role-{{ $role->id }}-perm-{{ $p->id }}"
                                   class="custom-control-input"
                                   {{ in_array($p->id, $rolePermIds) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="role-{{ $role->id }}-perm-{{ $p->id }}">
                                <strong>{{ $p->name }}</strong>
                                @if($p->description)<small class="d-block text-muted">{{ $p->description }}</small>@endif
                            </label>
                        </div>
                    </div>
                    @endforeach
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
