@extends('layouts.admin')
@section('page-title', 'Ayat Hafalan Mingguan')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <form method="GET" class="form-inline mr-auto">
            <label class="mr-2 mb-0 small">Tahun</label>
            <input type="number" name="tahun" value="{{ $tahun }}" class="form-control form-control-sm mr-2" style="width:100px">
            <button class="btn btn-sm btn-outline-primary">Tampilkan</button>
        </form>
        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#mAdd">
            <i class="fas fa-plus"></i> Tambah
        </button>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:60px">Tahun</th>
                    <th style="width:120px">Bulan</th>
                    <th style="width:90px">Minggu</th>
                    <th style="width:200px">Referensi</th>
                    <th>Isi</th>
                    <th style="width:140px" class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($verses as $v)
                <tr>
                    <td>{{ $v->tahun }}</td>
<td>{{ \Carbon\Carbon::createFromDate(\Carbon\Carbon::now()->year, (int) $v->bulan, 1)->locale('id')->isoFormat('MMMM') }}</td>                    <td>Minggu {{ $v->minggu }}</td>
                    <td><strong>{{ $v->referensi }}</strong></td>
                    <td><small>{{ \Illuminate\Support\Str::limit($v->isi, 120) }}</small></td>
                    <td class="text-right">
                        <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#mEdit{{ $v->id }}">Edit</button>
                        <form method="POST" action="{{ route('admin.jurnal.weekly-verses.destroy', $v) }}"
                            class="d-inline" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <div class="modal fade" id="mEdit{{ $v->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('admin.jurnal.weekly-verses.update', $v) }}" class="modal-content">
                            @csrf @method('PUT')
                            <div class="modal-header"><h5 class="modal-title">Edit Ayat</h5></div>
                            <div class="modal-body">
                                <div class="form-row">
                                    <div class="form-group col-md-4"><label>Tahun</label>
                                        <input type="number" name="tahun" value="{{ $v->tahun }}" class="form-control" required></div>
                                    <div class="form-group col-md-4"><label>Bulan</label>
                                        <select name="bulan" class="form-control" required>
                                            @foreach(range(1,12) as $m)
                                                <option value="{{ $m }}" {{ $v->bulan == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}</option>
                                            @endforeach
                                        </select></div>
                                    <div class="form-group col-md-4"><label>Minggu</label>
                                        <select name="minggu" class="form-control" required>
                                            @foreach(range(1,4) as $w)<option value="{{ $w }}" {{ $v->minggu == $w ? 'selected' : '' }}>Minggu {{ $w }}</option>@endforeach
                                        </select></div>
                                </div>
                                <div class="form-group"><label>Referensi</label>
                                    <input type="text" name="referensi" value="{{ $v->referensi }}" class="form-control" required></div>
                                <div class="form-group"><label>Isi Ayat</label>
                                    <textarea name="isi" rows="4" class="form-control" required>{{ $v->isi }}</textarea></div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
                                <button class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada ayat untuk tahun ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="mAdd" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.jurnal.weekly-verses.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Tambah Ayat Mingguan</h5></div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-md-4"><label>Tahun</label>
                        <input type="number" name="tahun" value="{{ now()->year }}" class="form-control" required></div>
                    <div class="form-group col-md-4"><label>Bulan</label>
                        <select name="bulan" class="form-control" required>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}</option>
                            @endforeach
                        </select></div>
                    <div class="form-group col-md-4"><label>Minggu</label>
                        <select name="minggu" class="form-control" required>
                            @foreach(range(1,4) as $w)<option value="{{ $w }}">Minggu {{ $w }}</option>@endforeach
                        </select></div>
                </div>
                <div class="form-group"><label>Referensi</label>
                    <input type="text" name="referensi" class="form-control" placeholder="Mazmur 23:1" required></div>
                <div class="form-group"><label>Isi Ayat</label>
                    <textarea name="isi" rows="4" class="form-control" required></textarea></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
