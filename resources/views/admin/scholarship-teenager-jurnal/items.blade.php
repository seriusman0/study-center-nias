@extends('layouts.admin')
@section('page-title', 'Item Jurnal Remaja Beasiswa')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif

<div class="row">
    {{-- Add form --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Tambah Item</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.jurnal-scholarship-teenager.items.store') }}">
                    @csrf
                    <div class="form-group">
                        <label class="small font-weight-bold">Kategori</label>
                        <select name="kategori" class="form-control form-control-sm" required>
                            @foreach($kategoriList as $k)
                            <option value="{{ $k }}">{{ ucfirst($k) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Label</label>
                        <input type="text" name="label" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Tipe Respon</label>
                        <select name="response_type" class="form-control form-control-sm" required>
                            <option value="check">Check (centang opsional)</option>
                            <option value="boolean">Boolean (SUDAH / BELUM)</option>
                            <option value="time_range">Waktu (jam mulai–selesai)</option>
                        </select>
                    </div>
                    <button class="btn btn-sm btn-primary w-100">Tambah</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Item list --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Daftar Item</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Kategori</th>
                            <th>Label</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $item->kategori }}</span></td>
                            <td>{{ $item->label }}</td>
                            <td>
                                @if($item->response_type === 'boolean')
                                    <span class="badge badge-info">SUDAH/BELUM</span>
                                @elseif($item->response_type === 'time_range')
                                    <span class="badge badge-warning">Waktu</span>
                                @else
                                    <span class="badge badge-light">Check</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-xs btn-outline-primary"
                                    data-toggle="modal" data-target="#editModal{{ $item->id }}">Edit</button>
                                <form method="POST" action="{{ route('admin.jurnal-scholarship-teenager.items.destroy', $item) }}"
                                    class="d-inline" onsubmit="return confirm('Hapus item ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>

                        {{-- Edit modal --}}
                        <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.jurnal-scholarship-teenager.items.update', $item) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Item</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Kategori</label>
                                                <select name="kategori" class="form-control form-control-sm">
                                                    @foreach($kategoriList as $k)
                                                    <option value="{{ $k }}" {{ $item->kategori === $k ? 'selected' : '' }}>{{ ucfirst($k) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Label</label>
                                                <input type="text" name="label" class="form-control form-control-sm" value="{{ $item->label }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Tipe Respon</label>
                                                <select name="response_type" class="form-control form-control-sm">
                                                    <option value="check" {{ $item->response_type === 'check' ? 'selected' : '' }}>Check</option>
                                                    <option value="boolean" {{ $item->response_type === 'boolean' ? 'selected' : '' }}>Boolean (SUDAH/BELUM)</option>
                                                    <option value="time_range" {{ $item->response_type === 'time_range' ? 'selected' : '' }}>Waktu</option>
                                                </select>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                                    id="active{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="active{{ $item->id }}">Aktif</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada item.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
