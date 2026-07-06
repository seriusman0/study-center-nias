@extends('layouts.admin')
@section('page-title', 'Jadwal Alkitab — Jurnal College')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible">{{ $errors->first() }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif

{{-- Anchor: "Hari ini = Hari ke-X" --}}
<div class="card card-primary card-outline mb-3"
     x-data="anchorPanel(@json($allItems->keyBy('day_no')), {{ $config->anchor_day_no }})">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Atur Jadwal Hari Ini</h3>
    </div>
    <div class="card-body">
        {{-- Current status --}}
        <div class="alert alert-light border mb-3 py-2">
            <strong>Saat ini:</strong>
            Hari ini <strong>({{ now()->locale('id')->isoFormat('D MMMM Y') }})</strong>
            = Hari ke-<strong>{{ $config->todayDayNo() }}</strong>
            @php $todayItem = $allItems->firstWhere('day_no', $config->todayDayNo()); @endphp
            @if($todayItem)
            &mdash; <span class="text-primary">{{ $todayItem->pl_text }}</span> / <span class="text-primary">{{ $todayItem->pb_text }}</span>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.jurnal-college.bible.anchor') }}">
            @csrf @method('PUT')
            {{-- anchor_date is always today --}}
            <input type="hidden" name="anchor_date" value="{{ now()->toDateString() }}">
            {{-- Keep form times from existing config --}}
            <input type="hidden" name="form_open_time" value="{{ substr($config->form_open_time, 0, 5) }}">
            <input type="hidden" name="form_close_time" value="{{ substr($config->form_close_time, 0, 5) }}">

            <div class="row align-items-end">
                <div class="col-md-3 form-group mb-0">
                    <label class="font-weight-bold">Ubah ke Hari ke-</label>
                    <input type="number" name="anchor_day_no" class="form-control"
                        min="1" max="366" x-model.number="dayNo"
                        @input="updatePreview()" required>
                    <small class="text-muted">Ketik nomor hari (1–366)</small>
                </div>
                <div class="col-md-6 form-group mb-0">
                    <label class="font-weight-bold">Preview</label>
                    <div class="form-control bg-light" style="min-height:38px">
                        <span x-show="preview.pl" class="text-dark">
                            Hari ke-<strong x-text="dayNo"></strong>:
                            <span class="text-primary" x-text="preview.pl"></span>
                            /
                            <span class="text-primary" x-text="preview.pb"></span>
                        </span>
                        <span x-show="!preview.pl" class="text-muted">Hari ke-<span x-text="dayNo"></span>: belum ada data</span>
                    </div>
                    <small class="text-muted">Besok otomatis menjadi hari ke-<span x-text="dayNo + 1 > 366 ? 1 : dayNo + 1"></span></small>
                </div>
                <div class="col-md-3 form-group mb-0">
                    <button type="submit" class="btn btn-primary btn-block"
                        onclick="return confirm('Terapkan hari ke-' + document.querySelector('[name=anchor_day_no]').value + ' untuk hari ini?')">
                        <i class="fas fa-check mr-1"></i> Terapkan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Form window settings --}}
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Jam Form Jurnal</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.jurnal-college.bible.anchor') }}" class="form-inline">
            @csrf @method('PUT')
            {{-- Keep anchor from existing config --}}
            <input type="hidden" name="anchor_day_no" value="{{ $config->anchor_day_no }}">
            <input type="hidden" name="anchor_date" value="{{ $config->anchor_date->toDateString() }}">
            <div class="form-group mr-3">
                <label class="mr-2 font-weight-bold">Buka</label>
                <input type="time" name="form_open_time" class="form-control"
                    value="{{ substr($config->form_open_time, 0, 5) }}" required>
            </div>
            <div class="form-group mr-3">
                <label class="mr-2 font-weight-bold">Tutup</label>
                <input type="time" name="form_close_time" class="form-control"
                    value="{{ substr($config->form_close_time, 0, 5) }}" required>
            </div>
            <button class="btn btn-outline-primary">Simpan Jam</button>
        </form>
        <small class="text-muted d-block mt-2">Pengguna college hanya bisa mengisi jurnal dalam rentang jam ini. Default: 06:00–23:59</small>
    </div>
</div>

{{-- Import JSON --}}
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-upload mr-2"></i>Import JSON Jadwal (366 hari)</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.jurnal-college.bible.import') }}" enctype="multipart/form-data" class="form-inline">
            @csrf
            <input type="file" name="json_file" accept=".json,.txt" class="form-control form-control-sm mr-2" required>
            <button class="btn btn-success btn-sm"><i class="fas fa-upload mr-1"></i>Import</button>
        </form>
        <small class="text-muted d-block mt-1">Format: <code>[{"no":1,"PL":"Kej 1-2","PB":"Mat 1:1-25"}, ...]</code></small>
    </div>
</div>

{{-- Schedule table --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar 366 Hari</h3>
        <div class="card-tools">
            <span class="badge badge-secondary mr-2">{{ $items->total() }} entri</span>
        </div>
    </div>
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="table table-sm table-hover mb-0" style="font-size:12px">
            <thead class="thead-light">
                <tr>
                    <th style="width:70px">Hari ke</th>
                    <th>Perjanjian Lama</th>
                    <th>Perjanjian Baru</th>
                    <th style="width:100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr x-data="{ editing: false, pl: @js($item->pl_text ?? ''), pb: @js($item->pb_text ?? '') }"
                    :class="pl === '' && pb === '' && 'table-warning'">
                    <td class="text-center font-weight-bold text-{{ $item->day_no == $config->todayDayNo() ? 'primary' : 'dark' }}">
                        {{ $item->day_no }}
                        @if($item->day_no == $config->todayDayNo())
                        <span class="badge badge-primary badge-sm ml-1">Hari ini</span>
                        @endif
                    </td>
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
                        <form method="POST" action="{{ route('admin.jurnal-college.bible.update', $item) }}" x-show="editing" style="display:none">
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
    <div class="card-footer">{{ $items->withQueryString()->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
function anchorPanel(allItems, currentDayNo) {
    return {
        dayNo: currentDayNo,
        preview: { pl: '', pb: '' },
        init() { this.updatePreview(); },
        updatePreview() {
            const n = parseInt(this.dayNo);
            if (n >= 1 && n <= 366 && allItems[n]) {
                this.preview.pl = allItems[n].pl_text || '';
                this.preview.pb = allItems[n].pb_text || '';
            } else {
                this.preview.pl = '';
                this.preview.pb = '';
            }
        }
    }
}
</script>
@endpush
@endsection
