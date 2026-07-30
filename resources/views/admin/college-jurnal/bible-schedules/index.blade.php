@extends('layouts.admin')
@section('page-title', 'Jadwal Pembacaan Alkitab')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible">{{ $errors->first() }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0"><i class="fas fa-book-open mr-2"></i>Daftar Jadwal Pembacaan Alkitab</h3>
        <a href="{{ route('admin.jurnal-college.bible-schedules.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Buat Jadwal Baru
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:50px">#</th>
                    <th>Nama Jadwal</th>
                    <th>Deskripsi</th>
                    <th style="width:80px" class="text-center">Entri</th>
                    <th style="width:60px" class="text-center">Status</th>
                    <th style="width:180px" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $schedule)
                <tr>
                    <td class="text-muted">{{ $schedule->id }}</td>
                    <td>
                        <strong>{{ $schedule->name }}</strong>
                    </td>
                    <td class="text-muted">{{ $schedule->description ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge badge-secondary">{{ $schedule->items_count }} / 366</span>
                    </td>
                    <td class="text-center">
                        @if($config->active_schedule_id == $schedule->id)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <form method="POST" action="{{ route('admin.jurnal-college.bible-schedules.set-active', $schedule) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-success" title="Jadikan aktif">Set Aktif</button>
                            </form>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.jurnal-college.bible', ['schedule_id' => $schedule->id]) }}"
                           class="btn btn-xs btn-outline-primary" title="Lihat 366 hari">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                        <a href="{{ route('admin.jurnal-college.bible-schedules.edit', $schedule) }}"
                           class="btn btn-xs btn-outline-secondary" title="Edit nama">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($config->active_schedule_id != $schedule->id)
                        <form method="POST" action="{{ route('admin.jurnal-college.bible-schedules.destroy', $schedule) }}" class="d-inline"
                              onsubmit="return confirm('Hapus jadwal \"{{ $schedule->name }}\"? Semua {{ $schedule->items_count }} entri hari ikut terhapus.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @else
                        <button class="btn btn-xs btn-outline-danger" disabled title="Tidak bisa hapus jadwal aktif">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada jadwal.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
