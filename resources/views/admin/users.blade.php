@extends('layouts.admin')

@section('page-title', auth()->user()->isAdmin() ? 'Pengguna' : 'Daftar Siswa')

@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp

<style>
.uf-bar{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem;align-items:center}
.uf-bar input,.uf-bar select{flex:1 1 160px;min-width:0;font-size:.8rem}
.uf-bar .btn{white-space:nowrap}
.uf-list{display:flex;flex-direction:column;gap:.5rem}
.uf-card{background:#fff;border:1px solid #e9ecef;border-radius:.65rem;padding:.75rem;display:flex;align-items:flex-start;gap:.65rem}
.uf-card:hover{border-color:#ced4da;box-shadow:0 2px 8px rgba(0,0,0,.07)}
.uf-avatar{width:38px;height:38px;border-radius:50%;object-fit:cover;flex-shrink:0}
.uf-body{flex:1;min-width:0}
.uf-name{font-weight:600;font-size:.85rem;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.uf-meta{font-size:.72rem;color:#6c757d;margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.uf-badges{display:flex;flex-wrap:wrap;gap:.25rem;margin-top:.35rem}
.uf-actions{display:flex;flex-direction:column;gap:.3rem;flex-shrink:0;align-items:flex-end}
.uf-actions .btn{font-size:.7rem;padding:.2rem .55rem;line-height:1.4}
</style>

{{-- Filter bar --}}
<form method="GET" action="{{ route('admin.users') }}" class="uf-bar">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / email / username"
           class="form-control form-control-sm">
    @if($isAdmin)
    <select name="role" class="form-control form-control-sm" onchange="this.form.submit()">
        <option value="">Semua Role</option>
        @foreach($roles as $role)
        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
        </option>
        @endforeach
    </select>
    <select name="cabang" class="form-control form-control-sm" onchange="this.form.submit()">
        <option value="">Semua Cabang</option>
        @foreach($cabangs as $cabang)
        <option value="{{ $cabang->id }}" {{ request('cabang') == $cabang->id ? 'selected' : '' }}>
            {{ $cabang->nama }}
        </option>
        @endforeach
    </select>
    @endif
    <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
    @if($isAdmin)
    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus"></i> Tambah
    </a>
    @endif
</form>

<p class="text-muted" style="font-size:.75rem;margin-bottom:.6rem">
    {{ $users->total() }} pengguna ditemukan
    @if(!$isAdmin && auth()->user()->cabang_id)
        · Cabang {{ auth()->user()->cabang?->nama }}
    @endif
</p>

{{-- User cards --}}
<div class="uf-list">
    @forelse($users as $user)
    <div class="uf-card">
        <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=76&background=1e3a5f&color=fff' }}"
             class="uf-avatar" alt="">
        <div class="uf-body">
            <div class="uf-name">{{ $user->name }}</div>
            <div class="uf-meta">{{ $user->username }}{{ $user->email ? ' · '.$user->email : '' }}</div>
            <div class="uf-meta">{{ $user->cabang?->nama ?? 'Tanpa cabang' }}</div>
            <div class="uf-badges">
                @forelse($user->roles as $r)
                    <span class="badge badge-info">{{ ucfirst(str_replace('_',' ',$r->name)) }}</span>
                @empty
                    <span class="badge badge-secondary">tanpa role</span>
                @endforelse
                @if($isAdmin)
                <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}" class="d-inline">
                    @csrf
                    <button type="submit"
                            class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }} border-0"
                            style="cursor:pointer;font-size:.7rem">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </button>
                </form>
                @else
                <span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }}">
                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
                @endif
            </div>
        </div>
        @if($isAdmin)
        <div class="uf-actions">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-info">Edit</a>
            <form method="POST" action="{{ route('admin.users.impersonate', $user->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning"
                        onclick="return confirm('Masuk sebagai {{ addslashes($user->name) }}?')"
                        title="Login sebagai pengguna ini tanpa password">
                    <i class="fas fa-user-secret"></i> Masuk
                </button>
            </form>
            <form method="POST" action="{{ route('admin.users.delete', $user->id) }}"
                  onsubmit="return confirm('Hapus pengguna ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
        </div>
        @endif
    </div>
    @empty
    <div class="text-center text-muted py-5" style="font-size:.85rem">Tidak ada pengguna ditemukan.</div>
    @endforelse
</div>

@if($users->lastPage() > 1)
<div class="mt-3">{{ $users->withQueryString()->links('pagination::bootstrap-4') }}</div>
@endif
@endsection
