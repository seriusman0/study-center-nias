@extends('layouts.admin')

@section('page-title', 'Terbitkan & Arsip Sertifikat')

@section('content')
<div class="row">
    {{-- Issue Form --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Terbitkan Sertifikat Baru</h6></div>
            <div class="card-body">
                @if($templates->isEmpty())
                    <div class="alert alert-warning small mb-0">
                        Belum ada template aktif. <a href="{{ route('admin.certificates.templates.create') }}">Buat template</a> terlebih dahulu.
                    </div>
                @else
                <form method="POST" action="{{ route('admin.certificates.issued.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Siswa <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control form-control-sm" required>
                            <option value="">-- Pilih siswa --</option>
                            @foreach($students as $s)
                            <option value="{{ $s->id }}" {{ old('user_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->cabang->nama ?? 'Pusat' }})
                            </option>
                            @endforeach
                        </select>
                        @error('user_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Template <span class="text-danger">*</span></label>
                        <select name="template_id" class="form-control form-control-sm" required>
                            <option value="">-- Pilih template --</option>
                            @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}" {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->nama }} ({{ ucfirst($tpl->orientation) }})
                            </option>
                            @endforeach
                        </select>
                        @error('template_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Nama Kursus / Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kursus" class="form-control form-control-sm"
                               list="kelas-list" value="{{ old('nama_kursus') }}" required maxlength="150">
                        <datalist id="kelas-list">
                            @foreach($kelasList as $k)
                            <option value="{{ $k->nama }}">
                            @endforeach
                        </datalist>
                        @error('nama_kursus')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lulus <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lulus" class="form-control form-control-sm"
                               value="{{ old('tanggal_lulus', date('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}" required>
                        @error('tanggal_lulus')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-pdf"></i> Terbitkan PDF
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Archive Table --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Arsip Sertifikat Terbit</h6>
                <form method="GET" class="d-flex" style="gap:6px">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Cari nama siswa..." value="{{ request('search') }}" style="width:160px">
                    <select name="template_id" class="form-control form-control-sm" style="width:160px">
                        <option value="">Semua template</option>
                        @foreach($templates as $tpl)
                        <option value="{{ $tpl->id }}" {{ request('template_id') == $tpl->id ? 'selected' : '' }}>
                            {{ $tpl->nama }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>No. Sertifikat</th>
                            <th>Siswa</th>
                            <th>Kursus</th>
                            <th>Tanggal Lulus</th>
                            <th>Diterbitkan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issued as $cert)
                        <tr>
                            <td><code style="font-size:11px">{{ $cert->nomor_sertifikat }}</code></td>
                            <td style="font-size:13px">{{ $cert->student->name ?? '-' }}</td>
                            <td style="font-size:13px">{{ $cert->nama_kursus }}</td>
                            <td style="font-size:12px">{{ $cert->tanggal_lulus->format('d/m/Y') }}</td>
                            <td style="font-size:12px">{{ $cert->issued_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.certificates.issued.download', $cert->id) }}"
                                   class="btn btn-xs btn-success" title="Download PDF">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.certificates.issued.destroy', $cert->id) }}"
                                      class="d-inline" onsubmit="return confirm('Hapus sertifikat ini permanen?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Belum ada sertifikat diterbitkan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($issued->hasPages())
            <div class="card-footer">{{ $issued->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
