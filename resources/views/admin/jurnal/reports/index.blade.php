@extends('layouts.admin')
@section('page-title', 'Laporan Jurnal')

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $totalToday }}</h3><p>Siswa mengisi jurnal hari ini</p></div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $totalWeek }}</h3><p>Total entry 7 hari terakhir</p></div>
            <div class="icon"><i class="fas fa-calendar-week"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Cari nama/username">
            @if($cabangs->isNotEmpty())
                <select name="cabang_id" class="form-control form-control-sm mr-2">
                    <option value="">Semua cabang</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                    @endforeach
                </select>
            @endif
            <button class="btn btn-sm btn-outline-primary">Filter</button>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Cabang</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td><small class="text-muted">{{ $s->username }}</small></td>
                    <td>{{ $s->cabang?->nama ?? '—' }}</td>
                    <td class="text-right">
                        <a href="{{ route('admin.jurnal.reports.show', $s) }}" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $students->links() }}</div>
</div>
@endsection
