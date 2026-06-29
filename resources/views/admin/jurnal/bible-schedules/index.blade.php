@extends('layouts.admin')
@section('page-title', 'Porsi Pembacaan Alkitab')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 align-items-center">
        <form method="GET" class="form-inline mr-auto">
            <label class="mr-2 mb-0 small">Tahun</label>
            <input type="number" name="tahun" value="{{ $tahun }}" class="form-control form-control-sm mr-2" style="width:90px">
            <label class="mr-2 mb-0 small">Bulan</label>
            <select name="bulan" class="form-control form-control-sm mr-2" style="width:130px">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-primary">Tampilkan</button>
        </form>
        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#mAdd">
            <i class="fas fa-plus"></i> Tambah
        </button>
        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#mBulk">
            <i class="fas fa-calendar-alt"></i> Bulk Range
        </button>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:140px">Tanggal</th>
                    <th>Perjanjian Lama</th>
                    <th>Perjanjian Baru</th>
                    <th style="width:140px" class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $s)
                <tr>
                    <td>{{ $s->tanggal->locale('id')->isoFormat('ddd, D MMM Y') }}</td>
                    <td>{{ $s->pl_porsi ?: '—' }}</td>
                    <td>{{ $s->pb_porsi ?: '—' }}</td>
                    <td class="text-right">
                        <button class="btn btn-sm btn-outline-secondary" data-toggle="modal"
                            data-target="#mEdit{{ $s->id }}">Edit</button>
                        <form method="POST" action="{{ route('admin.jurnal.bible-schedules.destroy', $s) }}"
                            class="d-inline" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <div class="modal fade" id="mEdit{{ $s->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('admin.jurnal.bible-schedules.update', $s) }}" class="modal-content">
                            @csrf @method('PUT')
                            <div class="modal-header"><h5 class="modal-title">Edit Porsi {{ $s->tanggal->toDateString() }}</h5></div>
                            <div class="modal-body">
                                <div class="form-group"><label>Tanggal</label>
                                    <input type="date" name="tanggal" value="{{ $s->tanggal->toDateString() }}" class="form-control" required></div>
                                <div class="form-group"><label>Perjanjian Lama</label>
                                    <input type="text" name="pl_porsi" value="{{ $s->pl_porsi }}" class="form-control" placeholder="Kejadian 1-3"></div>
                                <div class="form-group"><label>Perjanjian Baru</label>
                                    <input type="text" name="pb_porsi" value="{{ $s->pb_porsi }}" class="form-control" placeholder="Matius 1-2"></div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
                                <button class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada porsi untuk periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="mAdd" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.jurnal.bible-schedules.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Tambah Porsi</h5></div>
            <div class="modal-body">
                <div class="form-group"><label>Tanggal</label>
                    <input type="date" name="tanggal" value="{{ now()->toDateString() }}" class="form-control" required></div>
                <div class="form-group"><label>Perjanjian Lama</label>
                    <input type="text" name="pl_porsi" class="form-control" placeholder="Kejadian 1-3"></div>
                <div class="form-group"><label>Perjanjian Baru</label>
                    <input type="text" name="pb_porsi" class="form-control" placeholder="Matius 1-2"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="mBulk" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.jurnal.bible-schedules.bulk') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Bulk Range</h5></div>
            <div class="modal-body">
                <p class="small text-muted">Isi porsi yang sama untuk rentang tanggal (mis. seminggu).</p>
                <div class="form-row">
                    <div class="form-group col-md-6"><label>Dari</label>
                        <input type="date" name="from" class="form-control" required></div>
                    <div class="form-group col-md-6"><label>Sampai</label>
                        <input type="date" name="to" class="form-control" required></div>
                </div>
                <div class="form-group"><label>Perjanjian Lama</label>
                    <input type="text" name="pl_porsi" class="form-control"></div>
                <div class="form-group"><label>Perjanjian Baru</label>
                    <input type="text" name="pb_porsi" class="form-control"></div>
                <div class="form-check"><input type="checkbox" name="overwrite" value="1" class="form-check-input" id="ovw">
                    <label class="form-check-label" for="ovw">Timpa data yang sudah ada</label></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
                <button class="btn btn-primary">Proses</button>
            </div>
        </form>
    </div>
</div>
@endsection
