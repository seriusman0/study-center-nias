@extends('layouts.admin')
@section('page-title', 'Laporan: ' . $student->name)

@section('content')
<a href="{{ route('admin.jurnal.reports.index') }}" class="btn btn-sm btn-link mb-2"><i class="fas fa-arrow-left"></i> Kembali</a>

<div class="card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center">
        <div class="mr-auto">
            <h5 class="mb-0">{{ $student->name }}</h5>
            <small class="text-muted">@ {{ $student->username }} · {{ $student->cabang?->nama ?? '—' }}</small>
        </div>
        <div>
            <span class="badge badge-success" style="font-size:14px">{{ $matrix['pct'] }}%</span>
            <small class="text-muted ml-1">{{ $matrix['checked'] }} / {{ $matrix['total'] }} centang</small>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label class="mr-2 mb-0 small">Dari</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm mr-2">
            <label class="mr-2 mb-0 small">Sampai</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-sm btn-outline-primary mr-2">Tampilkan</button>
            <a href="{{ route('admin.jurnal.reports.export', ['student' => $student->id, 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"
               class="btn btn-sm btn-success"><i class="fas fa-file-csv"></i> Export CSV</a>
        </form>
    </div>
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="table table-sm table-bordered mb-0" style="font-size:12px">
            <thead class="thead-light">
                <tr>
                    @foreach($matrix['headers'] as $h)
                        <th class="text-nowrap">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($matrix['rows'] as $row)
                <tr>
                    @foreach($row as $i => $cell)
                        @if($i === 0)
                            <td class="text-nowrap">{{ $cell }}</td>
                        @else
                            <td class="text-center {{ $cell === 'Y' ? 'bg-success text-white' : 'text-muted' }}">{{ $cell === 'Y' ? '✓' : '–' }}</td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
