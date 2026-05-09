@extends('layouts.admin')

@section('page-title', auth()->user()->isAdmin() ? 'Pengguna' : 'Daftar Siswa')

@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <form method="GET" action="{{ route('admin.users') }}" class="form-inline">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama/email/username"
                   class="form-control form-control-sm mr-2" style="width:240px">
            @if($isAdmin)
            <select name="role" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                    {{ ucfirst($role->name) }}
                </option>
                @endforeach
            </select>
            @endif
            <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
        </form>
        @if($isAdmin)
        <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Pengguna
        </a>
        @else
        <span class="badge badge-info">
            Mode Lihat: Siswa
            @if(auth()->user()->cabang_id) · Cabang {{ auth()->user()->cabang?->nama }} @endif
        </span>
        @endif
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Cabang</th>
                    <th>Status</th>
                    @if($isAdmin)<th>Aksi</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=28&background=1e3a5f&color=fff' }}"
                                 class="img-circle mr-2" style="width:28px;height:28px;object-fit:cover" alt="">
                            {{ $user->name }}
                        </div>
                    </td>
                    <td class="text-muted" style="font-size:13px">{{ $user->username }}</td>
                    <td class="text-muted" style="font-size:13px">{{ $user->email ?? '-' }}</td>
                    <td>
                        @forelse($user->roles as $r)
                            <span class="badge badge-info mr-1">{{ ucfirst($r->name) }}</span>
                        @empty
                            <span class="text-muted" style="font-size:12px">- tanpa role -</span>
                        @endforelse
                    </td>
                    <td style="font-size:13px">{{ $user->cabang?->nama ?? '-' }}</td>
                    <td>
                        @if($isAdmin)
                        <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}">
                            @csrf
                            <button type="submit"
                                    class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }} border-0"
                                    style="cursor:pointer">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                        @else
                        <span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        @endif
                    </td>
                    @if($isAdmin)
                    <td>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-xs btn-info">Edit</a>
                        <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" class="d-inline"
                              onsubmit="return confirm('Hapus pengguna ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->lastPage() > 1)
    <div class="card-footer">
        {{ $users->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
