@extends('layouts.admin')
@section('page-title', 'Laporan Jurnal Remaja Beasiswa')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Jurnal Harian - Remaja Beasiswa</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Cari nama">
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
                    <th>Kelas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td><code>{{ $user->username }}</code></td>
                    <td>{{ $user->studentProfile?->grade_class ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.jurnal-scholarship-teenager.show', $user) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                        <a href="{{ route('admin.jurnal-scholarship-teenager.export', $user) }}" class="btn btn-xs btn-success">
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
