@extends('layouts.admin')
@section('page-title', 'Jadwal Pembacaan Alkitab - College')

@section('content')

{{-- Config panel --}}
<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Konfigurasi Jadwal & Jam Form</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.college.bible.anchor') }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-3 form-group">
                    <label class="small font-weight-bold">Hari ke (anchor)</label>
                    <input type="number" name="anchor_day_no" class="form-control form-control-sm" min="1" max="366"
                        value="{{ $config->anchor_day_no }}" required>
                    <small class="text-muted">Hari ke berapa pada tanggal anchor</small>
                </div>
                <div class="col-md-3 form-group">
                    <label class="small font-weight-bold">Tanggal anchor</label>
                    <input type="date" name="anchor_date" class="form-control form-control-sm"
                        value="{{ $config->anchor_date->toDateString() }}" required>
                </div>
                <div class="col-md-2 form-group">
                    <label class="small font-weight-bold">Form buka</label>
                    <input type="time" name="form_open_time" class="form-control form-control-sm"
                        value="{{ substr($config->form_open_time, 0, 5) }}" required>
                </div>
                <div class="col-md-2 form-group">
                    <label class="small font-weight-bold">Form tutup</label>
                    <input type="time" name="form_close_time" class="form-control form-control-sm"
                        value="{{ substr($config->form_close_time, 0, 5) }}" required>
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <button class="btn btn-sm btn-primary w-100">Simpan</button>
                </div>
            </div>
            @if(session('success'))
            <div class="alert alert-success alert-dismissible mt-2 py-2 px-3 mb-0">{{ session('success') }}
                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Import JSON --}}
<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Import JSON Jadwal (366 hari)</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.college.bible.import') }}" enctype="multipart/form-data" class="form-inline">
            @csrf
            <input type="file" name="json_file" accept=".json,.txt" class="form-control form-control-sm mr-2" required>
            <button class="btn btn-sm btn-success"><i class="fas fa-upload"></i> Import</button>
        </form>
    </div>
</div>

{{-- Schedule table --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar 366 Hari</h3>
        <div class="card-tools">
            <span class="badge badge-secondary">{{ $items->total() }} entri</span>
        </div>
    </div>
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="table table-sm table-hover mb-0" style="font-size:12px">
            <thead class="thead-light">
                <tr>
                    <th style="width:70px">Hari ke</th>
                    <th>Perjanjian Lama</th>
                    <th>Perjanjian Baru</th>
                    <th style="width:80px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr x-data="{ editing: false, pl: '{{ addslashes($item->pl_text ?? '') }}', pb: '{{ addslashes($item->pb_text ?? '') }}' }">
                    <td class="text-center font-weight-bold">{{ $item->day_no }}</td>
                    <td>
                        <span x-show="!editing">{{ $item->pl_text ?? '—' }}</span>
                        <input x-show="editing" x-model="pl" type="text" class="form-control form-control-sm" style="display:none">
                    </td>
                    <td>
                        <span x-show="!editing">{{ $item->pb_text ?? '—' }}</span>
                        <input x-show="editing" x-model="pb" type="text" class="form-control form-control-sm" style="display:none">
                    </td>
                    <td>
                        <button class="btn btn-xs btn-outline-secondary" x-show="!editing" @click="editing=true">Edit</button>
                        <form method="POST" action="{{ route('admin.college.bible.update', $item) }}" x-show="editing" style="display:none">
                            @csrf @method('PUT')
                            <input type="hidden" name="pl_text" :value="pl">
                            <input type="hidden" name="pb_text" :value="pb">
                            <button type="submit" class="btn btn-xs btn-success">Simpan</button>
                            <button type="button" class="btn btn-xs btn-secondary" @click="editing=false">Batal</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
    <div class="card-footer">{{ $items->links() }}</div>
    @endif
</div>
@endsection
