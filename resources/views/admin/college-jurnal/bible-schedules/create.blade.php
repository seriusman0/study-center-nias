@extends('layouts.admin')
@section('page-title', 'Buat Jadwal Pembacaan Alkitab')

@section('content')

<div class="card" style="max-width:600px">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus mr-2"></i>Buat Jadwal Baru</h3>
    </div>
    <form method="POST" action="{{ route('admin.jurnal-college.bible-schedules.store') }}">
        @csrf
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <label class="font-weight-bold">Nama Jadwal <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                       placeholder="contoh: Jadwal 2" required maxlength="255">
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Deskripsi</label>
                <input type="text" name="description" class="form-control" value="{{ old('description') }}"
                       placeholder="opsional" maxlength="500">
            </div>

            @if($schedules->isNotEmpty())
            <div class="form-group">
                <label class="font-weight-bold">Duplikat data dari jadwal lain</label>
                <select name="duplicate_from" class="form-control">
                    <option value="">— Mulai kosong —</option>
                    @foreach($schedules as $s)
                    <option value="{{ $s->id }}" {{ old('duplicate_from') == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                    @endforeach
                </select>
                <small class="text-muted">Jika dipilih, semua 366 entri akan disalin dari jadwal tersebut.</small>
            </div>
            @endif
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.jurnal-college.bible-schedules.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Buat Jadwal</button>
        </div>
    </form>
</div>

@endsection
