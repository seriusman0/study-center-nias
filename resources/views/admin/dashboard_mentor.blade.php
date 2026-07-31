@extends('layouts.admin')

@section('page-title', 'Dashboard Mentor')

@section('content')
<style>
.sc-stats{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1.25rem}
.sc-stat{padding:.3rem .8rem;border-radius:999px;background:#f1f3f5;font-size:.8rem;font-weight:600;text-decoration:none;color:#495057;border:1px solid #dee2e6}
.sc-stat:hover{background:#e9ecef;color:#212529;text-decoration:none}
.sc-section-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#adb5bd;margin:1rem 0 .5rem;padding-left:.1rem}
.sc-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.6rem}
@media(min-width:576px){.sc-grid{grid-template-columns:repeat(3,1fr)}}
.sc-card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.1rem .6rem;border-radius:.75rem;background:#fff;border:1px solid #e9ecef;text-decoration:none;color:inherit;min-height:90px;text-align:center;transition:box-shadow .15s,border-color .15s}
.sc-card:hover{box-shadow:0 3px 12px rgba(0,0,0,.09);border-color:#ced4da;text-decoration:none;color:inherit}
.sc-card i{font-size:1.6rem;margin-bottom:.35rem}
.sc-card span{font-size:.75rem;font-weight:600;line-height:1.3}
.sc-card small{font-size:.65rem;color:#adb5bd;margin-top:.1rem}
</style>

<p style="font-size:.85rem;margin-bottom:.75rem">
    Halo <strong>{{ $mentor->name }}</strong>.
    @if($stats['cabang_name'])
        Cabang: <strong>{{ $stats['cabang_name'] }}</strong>.
    @else
        <span class="text-muted">Belum terdaftar pada cabang manapun.</span>
    @endif
</p>

{{-- Stats pills --}}
<div class="sc-stats">
    <a href="{{ route('admin.users') }}" class="sc-stat"><i class="fas fa-user-graduate mr-1"></i> {{ $stats['students_in_cabang'] }} Siswa</a>
    <a href="{{ route('presensi.index') }}" class="sc-stat"><i class="fas fa-clipboard-check mr-1"></i> {{ $stats['my_presensi'] }} Presensi</a>
    <a href="{{ route('presensi.create') }}" class="sc-stat"><i class="fas fa-calendar-day mr-1"></i> {{ $stats['presensi_today'] }} Hari Ini</a>
</div>

{{-- Shortcut grid --}}
<div class="sc-section-label">Fitur</div>
<div class="sc-grid">
    <a href="{{ route('presensi.create') }}" class="sc-card">
        <i class="fas fa-clipboard-check" style="color:#e67700"></i>
        <span>Buat Presensi</span>
    </a>
    <a href="{{ route('presensi.index') }}" class="sc-card">
        <i class="fas fa-history" style="color:#e67700"></i>
        <span>Riwayat Presensi</span>
    </a>
    <a href="{{ route('admin.users') }}" class="sc-card">
        <i class="fas fa-users" style="color:#1971c2"></i>
        <span>Daftar Siswa</span>
    </a>
    <a href="{{ route('admin.jurnal.reports.index') }}" class="sc-card">
        <i class="fas fa-chart-bar" style="color:#2f9e44"></i>
        <span>Jurnal Siswa</span>
        <small>Laporan</small>
    </a>
    <a href="{{ route('admin.jurnal-college.index') }}" class="sc-card">
        <i class="fas fa-university" style="color:#2f9e44"></i>
        <span>Jurnal College</span>
    </a>
    <a href="{{ route('admin.jurnal-scholarship-teenager.index') }}" class="sc-card">
        <i class="fas fa-child" style="color:#2f9e44"></i>
        <span>Jurnal Remaja</span>
        <small>Beasiswa</small>
    </a>
</div>

{{-- Recent presensi --}}
<div class="sc-section-label" style="margin-top:1.25rem">5 Presensi Terbaru</div>
<div class="card mb-0">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Tanggal</th><th>Jam</th><th>Kelas</th><th>Siswa</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestPresensi as $p)
                <tr>
                    <td style="font-size:12px">{{ $p->tanggal->format('d M Y') }}</td>
                    <td style="font-size:12px">{{ substr($p->jam_mulai, 0, 5) }}–{{ substr($p->jam_selesai, 0, 5) }}</td>
                    <td style="font-size:12px">{{ $p->kelas }}</td>
                    <td><span class="badge badge-info">{{ $p->students_count }}</span></td>
                    <td><a href="{{ route('presensi.show', $p->id) }}" class="btn btn-xs btn-outline-info">Lihat</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4" style="font-size:13px">Belum ada presensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
