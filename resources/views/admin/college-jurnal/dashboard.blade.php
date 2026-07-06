@extends('layouts.admin')
@section('page-title', 'Jurnal College — Progress')

@section('content')

{{-- Top: Today's bible info + form window --}}
<div class="row mb-3">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-center px-3 border-right">
                        <div class="text-muted small">Hari ke</div>
                        <div class="font-weight-bold" style="font-size:2rem;line-height:1">{{ $dayNo }}</div>
                    </div>
                    <div class="flex-grow-1">
                        @if($bible)
                        <div class="font-weight-bold">{{ $bible->pl_text }} &nbsp;/&nbsp; {{ $bible->pb_text }}</div>
                        <div class="text-muted small mt-1">Jadwal pembacaan alkitab hari ini</div>
                        @else
                        <div class="text-muted">Jadwal hari ke-{{ $dayNo }} belum diisi.
                            <a href="{{ route('admin.jurnal-college.bible') }}">Atur sekarang</a>
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('admin.jurnal-college.bible') }}" class="btn btn-sm btn-outline-primary ml-3">
                        <i class="fas fa-cog"></i> Ubah Jadwal
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline {{ $config->isFormOpen() ? 'card-success' : 'card-warning' }}">
            <div class="card-body py-3 text-center">
                <div class="text-muted small mb-1">Jam Form Jurnal</div>
                <div class="font-weight-bold" style="font-size:1.1rem">
                    {{ substr($config->form_open_time, 0, 5) }} – {{ substr($config->form_close_time, 0, 5) }}
                </div>
                <span class="badge badge-{{ $config->isFormOpen() ? 'success' : 'warning' }} mt-1">
                    {{ $config->isFormOpen() ? 'Sedang Buka' : 'Sedang Tutup' }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Summary stats --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalUsers }}</h3>
                <p>Total Pengguna College</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('admin.jurnal-college.laporan') }}" class="small-box-footer">Lihat Laporan <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $activeToday }}</h3>
                <p>Aktif Hari Ini</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#users-table" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-{{ $activeToday > 0 ? 'primary' : 'secondary' }}">
            <div class="inner">
                <h3>{{ $totalUsers > 0 ? round($activeToday / $totalUsers * 100) : 0 }}%</h3>
                <p>Keaktifan Hari Ini</p>
            </div>
            <div class="icon"><i class="fas fa-chart-pie"></i></div>
            <a href="{{ route('admin.jurnal-college.bible') }}" class="small-box-footer">Pengaturan <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

{{-- User table --}}
<div class="card" id="users-table">
    <div class="card-header">
        <h3 class="card-title">Pengguna College</h3>
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
                    <th>Kampus</th>
                    <th class="text-center">7 Hari<br><small class="text-muted">Centang</small></th>
                    <th>Terakhir Aktif</th>
                    <th class="text-center">Hari Ini</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <strong>{{ $user->name }}</strong><br>
                        <small class="text-muted">{{ '@' . $user->username }}</small>
                    </td>
                    <td>{{ $user->collegeProfile?->institution_name ?? '—' }}</td>
                    <td class="text-center">
                        @php $cnt = $checkCounts[$user->id] ?? 0; @endphp
                        <span class="badge badge-{{ $cnt >= 10 ? 'success' : ($cnt >= 5 ? 'warning' : 'secondary') }}">{{ $cnt }}</span>
                    </td>
                    <td>
                        @php $last = $lastEntryDates[$user->id] ?? null; @endphp
                        {{ $last ? \Carbon\Carbon::parse($last)->locale('id')->isoFormat('D MMM Y') : '—' }}
                    </td>
                    <td class="text-center">
                        @if(isset($lastEntryDates[$user->id]) && $lastEntryDates[$user->id] === $today)
                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
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
                <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada pengguna college.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer">{{ $users->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
