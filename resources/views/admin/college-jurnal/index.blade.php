@extends('layouts.admin')
@section('page-title', 'Laporan Jurnal College')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Jurnal Harian - Pengguna College</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Cari nama">
                @if($campuses->isNotEmpty())
                <select name="campus" class="form-control form-control-sm mr-2">
                    <option value="">Semua Kampus</option>
                    @foreach($campuses as $campus)
                    <option value="{{ $campus }}" {{ request('campus') === $campus ? 'selected' : '' }}>{{ $campus }}</option>
                    @endforeach
                </select>
                @endif
                <button class="btn btn-sm btn-outline-primary">Filter</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Kampus</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td><code>{{ $user->username }}</code></td>
                    <td>{{ $user->collegeProfile?->institution_name ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.jurnal-college.show', $user) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                        <a href="{{ route('admin.jurnal-college.export', $user) }}" class="btn btn-xs btn-success">
                            <i class="fas fa-download"></i> CSV
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada pengguna college.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer">{{ $users->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
