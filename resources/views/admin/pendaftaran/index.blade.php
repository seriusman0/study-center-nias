@extends('layouts.admin')

@section('title', 'Pendaftaran Siswa')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Pendaftaran Siswa</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Pendaftaran</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                {{-- Filter tabs --}}
                <ul class="nav nav-pills">
                    @foreach(['semua' => 'Semua', 'pending' => 'Menunggu', 'perbaikan' => 'Perlu Perbaikan', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'] as $key => $label)
                    <li class="nav-item mr-1">
                        <a href="{{ route('admin.pendaftaran.index', ['status' => $key, 'search' => $search]) }}"
                           class="nav-link py-1 px-3 {{ $status === $key ? 'active' : '' }}">
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                <form method="GET" action="{{ route('admin.pendaftaran.index') }}" class="mt-3 d-flex" style="max-width:400px;gap:8px">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari nama pendaftar..."
                           class="form-control form-control-sm">
                    <button type="submit" class="btn btn-sm btn-primary">Cari</button>
                    @if($search)
                    <a href="{{ route('admin.pendaftaran.index', ['status' => $status]) }}" class="btn btn-sm btn-secondary">Reset</a>
                    @endif
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Sekolah</th>
                                <th>Kelas</th>
                                <th>Tanggal Daftar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendaftar as $i => $user)
                            @php $profile = $user->studentProfile; @endphp
                            <tr>
                                <td>{{ $pendaftar->firstItem() + $i }}</td>
                                <td>
                                    <strong>{{ $user->name }}</strong><br>
                                    <small class="text-muted">@{{ $user->username }}</small>
                                </td>
                                <td>{{ $profile?->school_name ?? '-' }}</td>
                                <td>{{ $profile?->grade_class ?? '-' }}</td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                <td>
                                    @php
                                        $st = $profile?->status ?? 'pending';
                                        $badge = match($st) {
                                            'diterima'  => 'success',
                                            'ditolak'   => 'danger',
                                            'perbaikan' => 'warning',
                                            default     => 'secondary',
                                        };
                                        $label = match($st) {
                                            'diterima'  => 'Diterima',
                                            'ditolak'   => 'Ditolak',
                                            'perbaikan' => 'Perlu Perbaikan',
                                            default     => 'Menunggu',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $badge }}">{{ $label }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.pendaftaran.show', $user) }}"
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Tidak ada data pendaftaran
                                    @if($search) dengan kata kunci "<strong>{{ $search }}</strong>" @endif.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($pendaftar->hasPages())
            <div class="card-footer">
                {{ $pendaftar->links() }}
            </div>
            @endif
        </div>

    </div>
</section>
@endsection
