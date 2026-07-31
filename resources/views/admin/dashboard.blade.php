@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible mb-3">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif

<style>
.sc-stats{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1.25rem}
.sc-stat{padding:.3rem .8rem;border-radius:999px;background:#f1f3f5;font-size:.8rem;font-weight:600;text-decoration:none;color:#495057;border:1px solid #dee2e6}
.sc-stat:hover{background:#e9ecef;color:#212529;text-decoration:none}
.sc-section-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#adb5bd;margin:1rem 0 .5rem;padding-left:.1rem}
.sc-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.6rem}
@media(min-width:576px){.sc-grid{grid-template-columns:repeat(3,1fr)}}
@media(min-width:992px){.sc-grid{grid-template-columns:repeat(4,1fr)}}
.sc-card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.1rem .6rem;border-radius:.75rem;background:#fff;border:1px solid #e9ecef;text-decoration:none;color:inherit;min-height:90px;text-align:center;transition:box-shadow .15s,border-color .15s}
.sc-card:hover{box-shadow:0 3px 12px rgba(0,0,0,.09);border-color:#ced4da;text-decoration:none;color:inherit}
.sc-card i{font-size:1.6rem;margin-bottom:.35rem}
.sc-card span{font-size:.75rem;font-weight:600;line-height:1.3}
.sc-card small{font-size:.65rem;color:#adb5bd;margin-top:.1rem;line-height:1.2}
</style>

{{-- Stats pills --}}
<div class="sc-stats">
    <a href="{{ route('admin.users') }}" class="sc-stat"><i class="fas fa-users mr-1"></i> {{ $stats['total_users'] }} Pengguna</a>
    <a href="{{ route('admin.cabangs') }}" class="sc-stat"><i class="fas fa-map-marker-alt mr-1"></i> {{ $stats['total_cabangs'] }} Cabang</a>
    <a href="{{ route('admin.blogs') }}" class="sc-stat"><i class="fas fa-newspaper mr-1"></i> {{ $stats['total_blogs'] }} Blog</a>
</div>

{{-- Pengguna --}}
<div class="sc-section-label">Pengguna</div>
<div class="sc-grid">
    <a href="{{ route('admin.users') }}" class="sc-card">
        <i class="fas fa-users" style="color:#1971c2"></i>
        <span>Daftar Pengguna</span>
    </a>
    <a href="{{ route('admin.pendaftaran.index') }}" class="sc-card">
        <i class="fas fa-user-plus" style="color:#1971c2"></i>
        <span>Pendaftaran</span>
        <small>Siswa baru</small>
    </a>
    <a href="{{ route('admin.users.create') }}" class="sc-card">
        <i class="fas fa-user-edit" style="color:#1971c2"></i>
        <span>Tambah Pengguna</span>
    </a>
</div>

{{-- Jurnal --}}
<div class="sc-section-label">Jurnal</div>
<div class="sc-grid">
    <a href="{{ route('admin.jurnal.life-items.index') }}" class="sc-card">
        <i class="fas fa-list-check" style="color:#2f9e44"></i>
        <span>Item Jurnal</span>
        <small>Siswa</small>
    </a>
    <a href="{{ route('admin.jurnal.reports.index') }}" class="sc-card">
        <i class="fas fa-chart-bar" style="color:#2f9e44"></i>
        <span>Laporan Jurnal</span>
        <small>Siswa</small>
    </a>
    <a href="{{ route('admin.jurnal-college.index') }}" class="sc-card">
        <i class="fas fa-university" style="color:#2f9e44"></i>
        <span>Progress College</span>
    </a>
    <a href="{{ route('admin.jurnal-college.laporan') }}" class="sc-card">
        <i class="fas fa-file-alt" style="color:#2f9e44"></i>
        <span>Laporan College</span>
    </a>
    <a href="{{ route('admin.jurnal-scholarship-teenager.index') }}" class="sc-card">
        <i class="fas fa-child" style="color:#2f9e44"></i>
        <span>Progress Remaja</span>
        <small>Beasiswa</small>
    </a>
    <a href="{{ route('admin.jurnal-scholarship-teenager.laporan') }}" class="sc-card">
        <i class="fas fa-file-alt" style="color:#2f9e44"></i>
        <span>Laporan Remaja</span>
        <small>Beasiswa</small>
    </a>
</div>

{{-- Presensi --}}
<div class="sc-section-label">Presensi</div>
<div class="sc-grid">
    <a href="{{ route('presensi.create') }}" class="sc-card">
        <i class="fas fa-clipboard-check" style="color:#e67700"></i>
        <span>Buat Presensi</span>
    </a>
    <a href="{{ route('presensi.index') }}" class="sc-card">
        <i class="fas fa-history" style="color:#e67700"></i>
        <span>Riwayat Presensi</span>
    </a>
    <a href="{{ route('admin.mentor-presensi.index') }}" class="sc-card">
        <i class="fas fa-user-clock" style="color:#e67700"></i>
        <span>Presensi Mentor</span>
    </a>
</div>

{{-- Pengaturan (admin only) --}}
@if(auth()->user()->isAdmin())
<div class="sc-section-label">Pengaturan</div>
<div class="sc-grid">
    <a href="{{ route('admin.announcements.index') }}" class="sc-card">
        <i class="fas fa-bullhorn" style="color:#6c757d"></i>
        <span>Pengumuman</span>
    </a>
    <a href="{{ route('admin.cabangs') }}" class="sc-card">
        <i class="fas fa-map-marker-alt" style="color:#6c757d"></i>
        <span>Cabang</span>
    </a>
    <a href="{{ route('admin.jurnal-college.bible-schedules.index') }}" class="sc-card">
        <i class="fas fa-bible" style="color:#6c757d"></i>
        <span>Jadwal Alkitab</span>
    </a>
    <a href="{{ route('admin.certificates.templates.index') }}" class="sc-card">
        <i class="fas fa-certificate" style="color:#6c757d"></i>
        <span>Sertifikat</span>
    </a>
    <a href="{{ route('admin.nametags') }}" class="sc-card">
        <i class="fas fa-id-card" style="color:#6c757d"></i>
        <span>Name Tags</span>
    </a>
    <a href="{{ route('admin.blogs') }}" class="sc-card">
        <i class="fas fa-newspaper" style="color:#6c757d"></i>
        <span>Blog</span>
    </a>
    <a href="{{ route('admin.roles') }}" class="sc-card">
        <i class="fas fa-user-tag" style="color:#6c757d"></i>
        <span>Roles</span>
    </a>
</div>
@endif
@endsection
