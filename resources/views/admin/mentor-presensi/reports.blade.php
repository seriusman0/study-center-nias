@extends('layouts.admin')
@section('page-title', 'Laporan Presensi Mentor')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-inline">
            <label class="mr-2 mb-0 small">Dari</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm mr-2">
            <label class="mr-2 mb-0 small">Sampai</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm mr-2">
            @if($cabangs->isNotEmpty())
            <select name="cabang_id" class="form-control form-control-sm mr-2">
                <option value="">Semua cabang</option>
                @foreach($cabangs as $c)
                    <option value="{{ $c->id }}" {{ $cabangId == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                @endforeach
            </select>
            @endif
            <button class="btn btn-sm btn-outline-primary mr-2">Filter</button>
            <a href="{{ route('admin.mentor-presensi.export.excel', request()->query()) }}" class="btn btn-sm btn-success mr-2">
                <i class="fas fa-file-csv"></i> Export Excel (CSV)
            </a>
            <a href="{{ route('admin.mentor-presensi.export.pdf', request()->query()) }}" class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $totals['sesi'] }}</h3><p>Total Sesi</p></div>
            <div class="icon"><i class="fas fa-clipboard-check"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $totals['jam'] }}</h3><p>Total Jam Mengajar</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $totals['murid'] }}</h3><p>Total Murid (sum)</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $totals['mentor_aktif'] }}</h3><p>Mentor Aktif</p></div>
            <div class="icon"><i class="fas fa-user-tie"></i></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h6 class="m-0">Trend Harian</h6></div>
    <div class="card-body"><canvas id="trendChart" height="80"></canvas></div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h6 class="m-0">Per Mentor</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Mentor</th>
                            <th class="text-center">Sesi</th>
                            <th class="text-center">Jam</th>
                            <th class="text-center">Total Murid</th>
                            <th class="text-center">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($perMentor as $r)
                        <tr>
                            <td>{{ $r->mentor?->name ?? '—' }}</td>
                            <td class="text-center">{{ $r->sesi }}</td>
                            <td class="text-center">{{ number_format($r->menit_total / 60, 2) }}</td>
                            <td class="text-center">{{ $r->murid_total }}</td>
                            <td class="text-center">{{ number_format((float) $r->murid_avg, 1) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h6 class="m-0">Per Cabang</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Cabang</th>
                            <th class="text-center">Sesi</th>
                            <th class="text-center">Total Murid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($perCabang as $r)
                        <tr>
                            <td>{{ $r->cabang?->nama ?? '—' }}</td>
                            <td class="text-center">{{ $r->sesi }}</td>
                            <td class="text-center">{{ $r->murid_total }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($trend->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))),
        datasets: [
            {
                label: 'Sesi',
                data: @json($trend->pluck('sesi')),
                borderColor: '#1e3a5f',
                backgroundColor: 'rgba(30,58,95,0.15)',
                tension: 0.3,
                yAxisID: 'y',
            },
            {
                label: 'Murid (sum)',
                data: @json($trend->pluck('murid')),
                borderColor: '#c9a84c',
                backgroundColor: 'rgba(201,168,76,0.15)',
                tension: 0.3,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y:  { beginAtZero: true, position: 'left', title: { display: true, text: 'Sesi' } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Murid' } },
        }
    }
});
</script>
@endpush
