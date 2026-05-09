@extends('layouts.admin')

@section('page-title', 'Detail Presensi')

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Sesi #{{ $presensi->id }}</h6>
                <div>
                    <a href="{{ route('presensi.edit', $presensi->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="{{ route('presensi.destroy', $presensi->id) }}" class="d-inline" onsubmit="return confirm('Hapus presensi ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th style="width:35%">Mentor</th><td>{{ $presensi->mentor?->name ?? '-' }}</td></tr>
                    <tr><th>Kelas</th><td>{{ $presensi->kelas }}</td></tr>
                    <tr><th>Cabang</th><td>{{ $presensi->cabang?->nama ?? '-' }}</td></tr>
                    <tr><th>Tanggal</th><td>{{ $presensi->tanggal->translatedFormat('l, j F Y') }}</td></tr>
                    <tr><th>Jam</th><td>{{ substr($presensi->jam_mulai, 0, 5) }} - {{ substr($presensi->jam_selesai, 0, 5) }}</td></tr>
                    <tr><th>Materi</th><td style="white-space:pre-wrap">{{ $presensi->materi }}</td></tr>
                    <tr><th>Dicatat oleh</th><td>{{ $presensi->creator?->name ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        @if($presensi->foto)
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Foto Kegiatan</h6></div>
            <div class="card-body p-0">
                <a href="{{ asset('storage/' . $presensi->foto) }}" target="_blank">
                    <img src="{{ asset('storage/' . $presensi->foto) }}" class="img-fluid" alt="Foto">
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Siswa ({{ $presensi->students->count() }})</h6>
        @php
            $hadir = $presensi->students->where('pivot.status', 'hadir')->count();
            $izin  = $presensi->students->where('pivot.status', 'izin')->count();
            $sakit = $presensi->students->where('pivot.status', 'sakit')->count();
            $alpha = $presensi->students->where('pivot.status', 'alpha')->count();
        @endphp
        <div>
            <span class="badge badge-success">Hadir {{ $hadir }}</span>
            <span class="badge badge-info">Izin {{ $izin }}</span>
            <span class="badge badge-warning">Sakit {{ $sakit }}</span>
            <span class="badge badge-danger">Alpha {{ $alpha }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="thead-light">
                <tr>
                    <th>#</th><th>Nama</th><th>Kelas</th><th>Sekolah</th><th>Cabang</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($presensi->students as $i => $s)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->studentProfile?->grade_class ?? '-' }}</td>
                    <td>{{ $s->studentProfile?->school_name ?? '-' }}</td>
                    <td>{{ $s->cabang?->nama ?? '-' }}</td>
                    <td>
                        @php
                            $st = $s->pivot->status;
                            $cls = ['hadir'=>'success','izin'=>'info','sakit'=>'warning','alpha'=>'danger'][$st] ?? 'secondary';
                        @endphp
                        <span class="badge badge-{{ $cls }}">{{ ucfirst($st) }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('presensi.index') }}" class="btn btn-secondary">&larr; Kembali</a>
</div>
@endsection
