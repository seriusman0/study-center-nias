@extends('layouts.admin')

@section('page-title', 'Presensi')

@section('content')
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline" style="gap:.5rem">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kelas/materi"
                   class="form-control form-control-sm" style="width:200px">
            <select name="mentor_id" class="form-control form-control-sm">
                <option value="">Semua Mentor</option>
                @foreach($mentors as $m)
                <option value="{{ $m->id }}" {{ request('mentor_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
            <select name="cabang_id" class="form-control form-control-sm">
                <option value="">Semua Cabang</option>
                @foreach($cabangs as $c)
                <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                @endforeach
            </select>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
            <span>s/d</span>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
            <a href="{{ route('presensi.index') }}" class="btn btn-sm btn-link">Reset</a>
            <span class="ml-auto"></span>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>{{ $presensi->total() }} catatan presensi</strong>
        <a href="{{ route('presensi.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i> Catat Presensi
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Mentor</th>
                    <th>Kelas</th>
                    <th>Cabang</th>
                    <th>Materi</th>
                    <th>Siswa</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($presensi as $p)
                <tr>
                    <td style="font-size:13px">{{ $p->tanggal->format('d M Y') }}</td>
                    <td style="font-size:13px">{{ substr($p->jam_mulai, 0, 5) }} - {{ substr($p->jam_selesai, 0, 5) }}</td>
                    <td style="font-size:13px">{{ $p->mentor?->name ?? '-' }}</td>
                    <td style="font-size:13px">{{ $p->kelas }}</td>
                    <td style="font-size:13px">{{ $p->cabang?->nama ?? '-' }}</td>
                    <td style="font-size:13px;max-width:240px" class="text-truncate" title="{{ $p->materi }}">{{ $p->materi }}</td>
                    <td><span class="badge badge-info">{{ $p->students_count }}</span></td>
                    <td>
                        <a href="{{ route('presensi.show', $p->id) }}" class="btn btn-xs btn-info">Lihat</a>
                        <a href="{{ route('presensi.edit', $p->id) }}" class="btn btn-xs btn-warning">Edit</a>
                        <form method="POST" action="{{ route('presensi.destroy', $p->id) }}" class="d-inline"
                              onsubmit="return confirm('Hapus presensi ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada presensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($presensi->lastPage() > 1)
    <div class="card-footer">
        {{ $presensi->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
