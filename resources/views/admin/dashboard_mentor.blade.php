@extends('layouts.admin')

@section('page-title', 'Dashboard Mentor')

@section('content')
<div class="alert alert-info" style="font-size:13px">
    <i class="fas fa-info-circle mr-1"></i>
    Halo <strong>{{ $mentor->name }}</strong>.
    @if($stats['cabang_name'])
        Anda mengelola siswa di cabang <strong>{{ $stats['cabang_name'] }}</strong>.
    @else
        Anda belum terdaftar pada cabang manapun. Hubungi admin agar daftar siswa dapat diakses.
    @endif
</div>

<div class="row">
    <div class="col-md-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['students_in_cabang'] }}</h3>
                <p>Siswa di Cabang Anda</p>
            </div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
            <a href="{{ route('admin.users') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['my_presensi'] }}</h3>
                <p>Total Presensi Saya</p>
            </div>
            <div class="icon"><i class="fas fa-clipboard-check"></i></div>
            <a href="{{ route('presensi.index') }}" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['presensi_today'] }}</h3>
                <p>Sesi Hari Ini</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
            <a href="{{ route('presensi.create') }}" class="small-box-footer">Catat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">5 Presensi Terbaru</h6></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Tanggal</th><th>Jam</th><th>Kelas</th><th>Cabang</th><th>Siswa</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestPresensi as $p)
                <tr>
                    <td style="font-size:13px">{{ $p->tanggal->format('d M Y') }}</td>
                    <td style="font-size:13px">{{ substr($p->jam_mulai, 0, 5) }} - {{ substr($p->jam_selesai, 0, 5) }}</td>
                    <td style="font-size:13px">{{ $p->kelas }}</td>
                    <td style="font-size:13px">{{ $p->cabang?->nama ?? '-' }}</td>
                    <td><span class="badge badge-info">{{ $p->students_count }}</span></td>
                    <td><a href="{{ route('presensi.show', $p->id) }}" class="btn btn-xs btn-info">Lihat</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada presensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
