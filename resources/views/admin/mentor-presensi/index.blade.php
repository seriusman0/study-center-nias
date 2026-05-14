@extends('layouts.admin')
@section('page-title', 'Daftar Presensi Mentor')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label class="mr-2 mb-0 small">Dari</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm mr-2">
            <label class="mr-2 mb-0 small">Sampai</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm mr-2">
            @if($cabangs->isNotEmpty())
            <select name="cabang_id" class="form-control form-control-sm mr-2">
                <option value="">Semua cabang</option>
                @foreach($cabangs as $c)
                    <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                @endforeach
            </select>
            @endif
            <select name="mentor_id" class="form-control form-control-sm mr-2">
                <option value="">Semua mentor</option>
                @foreach($mentors as $m)
                    <option value="{{ $m->id }}" {{ request('mentor_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-primary mr-2">Filter</button>
            <a href="{{ route('admin.mentor-presensi.reports', request()->query()) }}" class="btn btn-sm btn-success">
                <i class="fas fa-chart-line"></i> Lihat Laporan
            </a>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Mentor</th>
                    <th>Kelas</th>
                    <th>Cabang</th>
                    <th>Datang</th>
                    <th>Pulang</th>
                    <th class="text-center">Durasi (jam)</th>
                    <th class="text-center">Murid</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td>{{ $r->tanggal->toDateString() }}</td>
                    <td>{{ $r->mentor?->name ?? '—' }}</td>
                    <td>{{ $r->kelas?->nama ?? '—' }}</td>
                    <td><small>{{ $r->cabang?->nama ?? '—' }}</small></td>
                    <td>{{ substr($r->jam_datang, 0, 5) }}</td>
                    <td>{{ substr($r->jam_pulang, 0, 5) }}</td>
                    <td class="text-center">{{ number_format($r->durasi_menit / 60, 2) }}</td>
                    <td class="text-center">{{ $r->jumlah_murid }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada presensi pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $records->links() }}</div>
</div>
@endsection
