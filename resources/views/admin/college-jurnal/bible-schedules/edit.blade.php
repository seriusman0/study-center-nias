@extends('layouts.admin')
@section('page-title', 'Edit Jadwal — ' . $bibleSchedule->name)

@section('content')

<div class="card" style="max-width:600px">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit Jadwal</h3>
    </div>
    <form method="POST" action="{{ route('admin.jurnal-college.bible-schedules.update', $bibleSchedule) }}">
        @csrf @method('PUT')
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <label class="font-weight-bold">Nama Jadwal <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $bibleSchedule->name) }}" required maxlength="255">
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Deskripsi</label>
                <input type="text" name="description" class="form-control"
                       value="{{ old('description', $bibleSchedule->description) }}" maxlength="500">
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.jurnal-college.bible-schedules.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
        </div>
    </form>
</div>

@endsection
