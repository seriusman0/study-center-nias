@extends('layouts.admin')

@section('page-title', 'Template Sertifikat')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('admin.certificates.templates.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Buat Template Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Daftar Template Sertifikat</h6></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nama</th>
                    <th>Orientasi</th>
                    <th>Ukuran</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $tpl)
                <tr class="{{ $tpl->trashed() ? 'table-secondary text-muted' : '' }}">
                    <td>
                        <strong>{{ $tpl->nama }}</strong>
                        @if($tpl->deskripsi)
                            <small class="d-block text-muted">{{ Str::limit($tpl->deskripsi, 60) }}</small>
                        @endif
                        @if($tpl->trashed())
                            <span class="badge badge-danger">Dihapus</span>
                        @endif
                    </td>
                    <td>{{ ucfirst($tpl->orientation) }}</td>
                    <td>{{ strtoupper($tpl->paper_size) }}</td>
                    <td>
                        @if(! $tpl->trashed())
                            <span class="badge {{ $tpl->is_active ? 'badge-success' : 'badge-secondary' }}">
                                {{ $tpl->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        @endif
                    </td>
                    <td style="font-size:13px">{{ $tpl->creator->name ?? '-' }}</td>
                    <td style="font-size:12px">{{ $tpl->created_at->format('d/m/Y') }}</td>
                    <td>
                        @if(! $tpl->trashed())
                        <a href="{{ route('admin.certificates.templates.preview.saved', $tpl->id) }}"
                           class="btn btn-xs btn-secondary" target="_blank" title="Preview PDF">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.certificates.templates.edit', $tpl->id) }}"
                           class="btn btn-xs btn-info">Edit</a>
                        <form method="POST" action="{{ route('admin.certificates.templates.destroy', $tpl->id) }}"
                              class="d-inline" onsubmit="return confirm('Hapus template ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Belum ada template.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($templates->hasPages())
    <div class="card-footer">{{ $templates->links() }}</div>
    @endif
</div>
@endsection
