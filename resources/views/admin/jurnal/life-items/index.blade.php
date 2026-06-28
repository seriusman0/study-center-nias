@extends('layouts.admin')
@section('page-title', 'Template Jadwal Kehidupan')

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h6 class="mb-0 mr-auto">Template Item (berlaku global)</h6>
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#mAdd"><i class="fas fa-plus"></i> Tambah Item</button>
            </div>
            <div class="card-body p-0">
                @php
                    $labels = ['kerohanian' => 'Kerohanian', 'pendidikan' => 'Pendidikan', 'karakter' => 'Karakter'];
                @endphp
                @foreach($labels as $key => $label)
                    <div class="border-bottom">
                        <div class="px-3 py-2 bg-light"><strong>{{ $label }}</strong></div>
                        @forelse(($templates[$key] ?? collect()) as $it)
                            <div class="d-flex align-items-center px-3 py-2 border-top">
                                <span class="mr-auto">
                                    {{ $it->label }}
                                    @if($it->is_default)<span class="badge badge-info ml-1">default</span>@endif
                                    @if(!$it->is_active)<span class="badge badge-secondary ml-1">non-aktif</span>@endif
                                </span>
                                <button class="btn btn-sm btn-link" data-toggle="modal" data-target="#mEdit{{ $it->id }}">Edit</button>
                                <form method="POST" action="{{ route('admin.jurnal.life-items.destroy', $it) }}" class="d-inline" onsubmit="return confirm('Hapus item ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger">Hapus</button>
                                </form>
                            </div>
                            <div class="modal fade" id="mEdit{{ $it->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('admin.jurnal.life-items.update', $it) }}" class="modal-content">
                                        @csrf @method('PUT')
                                        <div class="modal-header"><h5 class="modal-title">Edit Item</h5></div>
                                        <div class="modal-body">
                                            <div class="form-group"><label>Kategori</label>
                                                <select name="kategori" class="form-control" required>
                                                    @foreach($labels as $kk => $ll)<option value="{{ $kk }}" {{ $it->kategori == $kk ? 'selected' : '' }}>{{ $ll }}</option>@endforeach
                                                </select></div>
                                            <div class="form-group"><label>Label</label>
                                                <input type="text" name="label" value="{{ $it->label }}" class="form-control" required></div>
                                            <div class="form-check">
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="checkbox" name="is_active" value="1" id="act{{ $it->id }}" class="form-check-input" {{ $it->is_active ? 'checked' : '' }}>
                                                <label for="act{{ $it->id }}" class="form-check-label">Aktif</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
                                            <button class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="px-3 py-2 text-muted small border-top">— belum ada item —</div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Tugaskan ke Siswa</h6></div>
            <div class="card-body">
                <p class="small text-muted">Pilih siswa untuk mengelola item yang berlaku padanya.</p>
                <div class="list-group" style="max-height:520px;overflow:auto">
                    @forelse($students as $st)
                        <a href="{{ route('admin.jurnal.life-items.student', $st) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>{{ $st->name }} <small class="text-muted">@ {{ $st->username }}</small></span>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    @empty
                        <p class="text-center text-muted py-3 mb-0">Tidak ada siswa.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mAdd" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.jurnal.life-items.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Tambah Item Template</h5></div>
            <div class="modal-body">
                <div class="form-group"><label>Kategori</label>
                    <select name="kategori" class="form-control" required>
                        <option value="kerohanian">Kerohanian</option>
                        <option value="pendidikan">Pendidikan</option>
                        <option value="karakter">Karakter</option>
                    </select></div>
                <div class="form-group"><label>Label</label>
                    <input type="text" name="label" class="form-control" required maxlength="150"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
