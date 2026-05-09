@extends('layouts.admin')

@section('page-title', 'Edit Pengguna')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-row align-items-center mb-3">
                <div class="col-auto">
                    <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=80&background=1e3a5f&color=fff' }}"
                         class="img-circle" style="width:64px;height:64px;object-fit:cover" alt="">
                </div>
                <div class="col">
                    <strong>{{ $user->name }}</strong><br>
                    <small class="text-muted">@{{ $user->username }}</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required pattern="[a-z0-9]+">
                    <small class="text-muted">huruf kecil &amp; angka</small>
                </div>
                <div class="form-group col-md-3">
                    <label>Password Baru</label>
                    <input type="text" name="password" class="form-control" placeholder="kosong = tidak diubah">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                </div>
                <div class="form-group col-md-3">
                    <label>Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $user->tempat_lahir) }}">
                </div>
                <div class="form-group col-md-3">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control"
                           value="{{ old('tanggal_lahir', optional($user->tanggal_lahir)->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>No HP Orangtua</label>
                    <input type="text" name="no_hp_orangtua" class="form-control" value="{{ old('no_hp_orangtua', $user->no_hp_orangtua) }}">
                </div>
                <div class="form-group col-md-5">
                    <label>Nama Sekolah</label>
                    <input type="text" name="nama_sekolah" class="form-control" value="{{ old('nama_sekolah', $user->nama_sekolah) }}">
                </div>
                <div class="form-group col-md-3">
                    <label>Tahun Masuk</label>
                    <input type="number" name="tahun_masuk" class="form-control" min="2000" max="2100" value="{{ old('tahun_masuk', $user->tahun_masuk) }}">
                </div>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $user->alamat) }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Role</label>
                    <select name="role_id" class="form-control">
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Cabang</label>
                    <select name="cabang_id" class="form-control">
                        <option value="">- Tidak ada -</option>
                        @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" {{ old('cabang_id', $user->cabang_id) == $cabang->id ? 'selected' : '' }}>
                            {{ $cabang->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Foto Profil</label>
                    <input type="file" name="avatar" class="form-control-file" accept="image/*">
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="is_active" value="1" id="isActive"
                               class="custom-control-input" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="isActive">Aktif</label>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <a href="{{ route('admin.users') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
