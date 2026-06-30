@extends('layouts.admin')

@section('page-title', 'Laporan Lengkap Presensi')

@section('content')

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" id="filterForm" class="form-inline" style="gap:.5rem">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kelas/materi"
                   class="form-control form-control-sm" style="width:200px">
            @if(auth()->user()->isAdmin())
            <select name="mentor_id" class="form-control form-control-sm"
                    onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Mentor</option>
                @foreach($mentors as $m)
                <option value="{{ $m->id }}" {{ request('mentor_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
            @endif
            <select name="cabang_id" class="form-control form-control-sm"
                    onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Cabang</option>
                @foreach($cabangs as $c)
                <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                @endforeach
            </select>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                   class="form-control form-control-sm"
                   onchange="document.getElementById('filterForm').submit()">
            <span>s/d</span>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                   class="form-control form-control-sm"
                   onchange="document.getElementById('filterForm').submit()">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
            <a href="{{ route('presensi.report') }}" class="btn btn-sm btn-link">Reset</a>
            <a href="{{ route('presensi.index', request()->query()) }}" class="btn btn-sm btn-link ml-2">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
        </form>
    </div>
</div>

<div class="row mb-3">
    <div class="col-6 col-md-4 col-lg-2 mb-2">
        <div class="small-box bg-secondary" style="min-height:0">
            <div class="inner py-2 px-3">
                <h5 class="mb-0">{{ $summary['total_kelas'] }}</h5>
                <p class="mb-0" style="font-size:12px">Total Kelas</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2 mb-2">
        <div class="small-box bg-success" style="min-height:0">
            <div class="inner py-2 px-3">
                <h5 class="mb-0">{{ $summary['hadir'] }}</h5>
                <p class="mb-0" style="font-size:12px">Hadir</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2 mb-2">
        <div class="small-box bg-warning" style="min-height:0">
            <div class="inner py-2 px-3">
                <h5 class="mb-0">{{ $summary['izin'] }}</h5>
                <p class="mb-0" style="font-size:12px">Izin</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2 mb-2">
        <div class="small-box bg-info" style="min-height:0">
            <div class="inner py-2 px-3">
                <h5 class="mb-0">{{ $summary['sakit'] }}</h5>
                <p class="mb-0" style="font-size:12px">Sakit</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2 mb-2">
        <div class="small-box bg-danger" style="min-height:0">
            <div class="inner py-2 px-3">
                <h5 class="mb-0">{{ $summary['alpha'] }}</h5>
                <p class="mb-0" style="font-size:12px">Alpha</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2 mb-2">
        <div class="small-box bg-dark" style="min-height:0">
            <div class="inner py-2 px-3">
                <h5 class="mb-0">{{ $summary['total_siswa'] }}</h5>
                <p class="mb-0" style="font-size:12px">Total Siswa</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <strong>{{ $rows->total() }} baris ditemukan</strong>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Tanggal</th>
                    <th>Mentor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                <tr>
                    <td style="font-size:13px">{{ $r->siswa_name }}</td>
                    <td style="font-size:13px">{{ $r->kelas }}</td>
                    <td style="font-size:13px">{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</td>
                    <td style="font-size:13px">{{ $r->mentor_name ?? '-' }}</td>
                    <td>
                        @php
                            $badge = match($r->status) {
                                'hadir' => 'success',
                                'izin'  => 'warning',
                                'sakit' => 'info',
                                'alpha' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge badge-{{ $badge }}">{{ ucfirst($r->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data untuk filter yang dipilih.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rows->lastPage() > 1)
    <div class="card-footer">
        {{ $rows->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

@endsection
