@extends('layouts.admin')
@section('page-title', 'Template Jurnal Offline')

@section('content')
<a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-link mb-3">
    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
</a>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div class="row">
    {{-- Form upload --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">
                <i class="fas fa-upload mr-1"></i> Upload Template PDF
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Upload file PDF template jurnal harian untuk tiap cabang.
                    Satu cabang bisa memiliki beberapa versi template.
                </p>

                @if($errors->any())
                    <div class="alert alert-danger py-2 small">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.jurnal.offline-templates.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="small font-weight-bold">Cabang <span class="text-danger">*</span></label>
                        <select name="cabang_id" class="form-control form-control-sm" required>
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabangs as $c)
                                <option value="{{ $c->id }}" {{ old('cabang_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">File PDF <span class="text-danger">*</span></label>
                        <input type="file" name="file" accept=".pdf" class="form-control-file" required>
                        <small class="text-muted">Maks. 10 MB, hanya PDF.</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Daftar template per cabang --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header font-weight-bold">
                <i class="fas fa-folder-open mr-1"></i> Template Tersimpan
            </div>
            <div class="card-body p-0">
                @if($templates->isEmpty())
                    <p class="text-center text-muted py-4">Belum ada template diupload.</p>
                @else
                    @foreach($cabangs as $cabang)
                        @if($templates->has($cabang->id))
                            <div class="px-3 pt-3 pb-1">
                                <div class="font-weight-bold text-uppercase small text-muted mb-2">
                                    <i class="fas fa-map-marker-alt mr-1"></i> {{ $cabang->nama }}
                                </div>
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Nama File</th>
                                            <th class="text-nowrap" style="width:130px">Diupload</th>
                                            <th class="text-nowrap" style="width:100px">Oleh</th>
                                            <th style="width:100px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($templates[$cabang->id] as $tpl)
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-pdf text-danger mr-1"></i>
                                                {{ $tpl->original_name }}
                                            </td>
                                            <td class="text-muted small">
                                                {{ $tpl->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}
                                            </td>
                                            <td class="small text-muted">{{ $tpl->uploader?->name ?? '—' }}</td>
                                            <td class="text-right">
                                                <a href="{{ route('admin.jurnal.offline-templates.download', $tpl) }}"
                                                   class="btn btn-xs btn-outline-primary" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form method="POST"
                                                      action="{{ route('admin.jurnal.offline-templates.destroy', $tpl) }}"
                                                      style="display:inline"
                                                      onsubmit="return confirm('Hapus template ini?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-xs btn-outline-danger" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <hr class="my-2">
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
