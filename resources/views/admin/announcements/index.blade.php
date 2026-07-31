@extends('layouts.admin')

@section('page-title', 'Pengumuman')

@section('content')
<style>
.ann-badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.7rem;font-weight:700;text-transform:uppercase}
.ann-info{background:#dbeafe;color:#1e40af}
.ann-success{background:#dcfce7;color:#166534}
.ann-warning{background:#fef9c3;color:#854d0e}
.ann-danger{background:#fee2e2;color:#991b1b}
</style>

<div class="d-flex justify-content-between align-items-center mb-3" style="flex-wrap:wrap;gap:.5rem">
    <span class="text-muted" style="font-size:.8rem">{{ $announcements->total() }} pengumuman</span>
    <a href="{{ route('admin.announcements.create') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus mr-1"></i> Buat Pengumuman
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Judul</th>
                    <th>Tipe</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $ann)
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:.85rem">{{ $ann->title }}</div>
                        <div class="text-muted" style="font-size:.72rem;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $ann->content }}</div>
                    </td>
                    <td>
                        <span class="ann-badge ann-{{ $ann->type }}">{{ $ann->type }}</span>
                    </td>
                    <td style="font-size:.75rem">
                        @if($ann->starts_at || $ann->ends_at)
                            {{ $ann->starts_at?->format('d M Y') ?? '∞' }}
                            –
                            {{ $ann->ends_at?->format('d M Y') ?? '∞' }}
                        @else
                            <span class="text-muted">Selamanya</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.announcements.toggle', $ann->id) }}" class="d-inline">
                            @csrf
                            <button type="submit"
                                    class="badge badge-{{ $ann->isCurrentlyActive() ? 'success' : 'secondary' }} border-0"
                                    style="cursor:pointer;font-size:.72rem">
                                {{ $ann->isCurrentlyActive() ? 'Aktif' : ($ann->is_active ? 'Terjadwal' : 'Nonaktif') }}
                            </button>
                        </form>
                    </td>
                    <td style="font-size:.75rem;color:#6c757d">
                        {{ $ann->creator->name ?? '-' }}<br>
                        {{ $ann->created_at->format('d M Y') }}
                    </td>
                    <td style="white-space:nowrap">
                        <a href="{{ route('admin.announcements.edit', $ann->id) }}" class="btn btn-xs btn-info">Edit</a>
                        <form method="POST" action="{{ route('admin.announcements.destroy', $ann->id) }}" class="d-inline"
                              onsubmit="return confirm('Hapus pengumuman ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4" style="font-size:.85rem">Belum ada pengumuman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($announcements->lastPage() > 1)
    <div class="card-footer">{{ $announcements->links('pagination::bootstrap-4') }}</div>
    @endif
</div>
@endsection
