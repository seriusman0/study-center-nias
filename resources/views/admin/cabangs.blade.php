@extends('layouts.admin')

@section('page-title', 'Cabang')

@section('content')
<div class="row">
    {{-- Add Form --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h6 class="m-0">Tambah Cabang</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.cabangs.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Nama Cabang</label>
                        <input type="text" name="nama" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" name="alamat" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label>Kontak</label>
                        <input type="text" name="kontak" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label>Link Grup WhatsApp <span class="text-muted">(opsional)</span></label>
                        <input type="url" name="whatsapp_link" class="form-control form-control-sm"
                               placeholder="https://chat.whatsapp.com/...">
                    </div>
                    <div class="form-group">
                        <label>Batas Kelas <span class="text-muted">(opsional)</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" name="kelas_min" class="form-control form-control-sm" min="1" max="12" placeholder="Min">
                            <span class="mx-1">–</span>
                            <input type="number" name="kelas_max" class="form-control form-control-sm" min="1" max="12" placeholder="Max">
                        </div>
                        <small class="text-muted">SD: 1–6 &bull; SMP: 7–9 &bull; SMA/SMK: 10–12. Kosongkan jika tidak dibatasi.</small>
                    </div>
                    <div class="form-group">
                        <label>Mata Pelajaran</label>
                        @foreach($masterMapel as $mp)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="add-mp-{{ $mp->id }}"
                                   name="mata_pelajaran[]" value="{{ $mp->nama }}" checked>
                            <label class="form-check-label" for="add-mp-{{ $mp->id }}">{{ $mp->nama }}</label>
                        </div>
                        @endforeach
                        <small class="text-muted">Pilih minimal 1. <a href="{{ route('admin.mata-pelajaran') }}">Kelola master mata pelajaran →</a></small>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="foto_wajib_add" name="foto_wajib" value="1" checked>
                        <label class="form-check-label" for="foto_wajib_add">Foto wajib saat pendaftaran</label>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="pendaftaran_buka_add" name="pendaftaran_buka" value="1" checked>
                        <label class="form-check-label" for="pendaftaran_buka_add">Pendaftaran dibuka</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary btn-block">Tambah</button>
                </form>
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h6 class="m-0">Daftar Cabang</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th>Alamat</th>
                            <th>Kontak</th>
                            <th>Kelas</th>
                            <th>Foto</th>
                            <th>Pendaftaran</th>
                            <th>WA</th>
                            <th>Blog</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cabangs as $cabang)
                        <tr>
                            <td>{{ $cabang->nama }}</td>
                            <td class="text-muted" style="font-size:12px">{{ $cabang->slug }}</td>
                            <td style="font-size:13px">{{ $cabang->alamat ?? '-' }}</td>
                            <td style="font-size:13px">{{ $cabang->kontak ?? '-' }}</td>
                            <td style="font-size:13px">
                                @if($cabang->kelas_min && $cabang->kelas_max)
                                    <span class="badge badge-info">{{ $cabang->kelas_min }}–{{ $cabang->kelas_max }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($cabang->foto_wajib)
                                    <span class="badge badge-danger">Wajib</span>
                                @else
                                    <span class="badge badge-secondary">Opsional</span>
                                @endif
                            </td>
                            <td>
                                @if($cabang->pendaftaran_buka)
                                    <span class="badge badge-success">Buka</span>
                                @else
                                    <span class="badge badge-danger">Tutup</span>
                                @endif
                            </td>
                            <td>
                                @if($cabang->whatsapp_link)
                                    <span class="badge badge-success">Ada</span>
                                @else
                                    <span class="badge badge-secondary">-</span>
                                @endif
                            </td>
                            <td>{{ $cabang->blogs_count }}</td>
                            <td>
                                @php
                                    $mp = $cabang->mata_pelajaran ?? ['Matematika','B.Inggris','B.Mandarin','Komputer'];
                                @endphp
                                <button type="button" class="btn btn-xs btn-info"
                                        data-id="{{ $cabang->id }}"
                                        data-nama="{{ $cabang->nama }}"
                                        data-alamat="{{ $cabang->alamat }}"
                                        data-kontak="{{ $cabang->kontak }}"
                                        data-foto-wajib="{{ $cabang->foto_wajib ? '1' : '0' }}"
                                        data-pendaftaran-buka="{{ $cabang->pendaftaran_buka ? '1' : '0' }}"
                                        data-wa-link="{{ $cabang->whatsapp_link ?? '' }}"
                                        data-kelas-min="{{ $cabang->kelas_min ?? '' }}"
                                        data-kelas-max="{{ $cabang->kelas_max ?? '' }}"
                                        data-mata-pelajaran="{{ e(json_encode($mp)) }}"
                                        onclick="openEdit(this)">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.cabangs.delete', $cabang->id) }}"
                                      class="d-inline" onsubmit="return confirm('Hapus cabang ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Edit Cabang</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="nama" id="edit-nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="alamat" id="edit-alamat" class="form-control">
                </div>
                <div class="form-group">
                    <label>Kontak</label>
                    <input type="text" name="kontak" id="edit-kontak" class="form-control">
                </div>
                <div class="form-group">
                    <label>Link Grup WhatsApp <span class="text-muted">(opsional)</span></label>
                    <input type="url" name="whatsapp_link" id="edit-whatsapp-link" class="form-control"
                           placeholder="https://chat.whatsapp.com/...">
                </div>
                <div class="form-group">
                    <label>Batas Kelas <span class="text-muted">(opsional)</span></label>
                    <div class="d-flex align-items-center">
                        <input type="number" name="kelas_min" id="edit-kelas-min" class="form-control" min="1" max="12" placeholder="Min">
                        <span class="mx-2">–</span>
                        <input type="number" name="kelas_max" id="edit-kelas-max" class="form-control" min="1" max="12" placeholder="Max">
                    </div>
                    <small class="text-muted">Kosongkan jika tidak dibatasi.</small>
                </div>
                <div class="form-group">
                    <label>Mata Pelajaran</label>
                    <div id="edit-mata-pelajaran-list">
                        @foreach($masterMapel as $mp)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input edit-mp-check"
                                   id="edit-mp-{{ $mp->id }}"
                                   name="mata_pelajaran[]"
                                   value="{{ $mp->nama }}">
                            <label class="form-check-label" for="edit-mp-{{ $mp->id }}">{{ $mp->nama }}</label>
                        </div>
                        @endforeach
                    </div>
                    <small class="text-muted">Pilih minimal 1. <a href="{{ route('admin.mata-pelajaran') }}">Kelola master →</a></small>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="edit-foto-wajib" name="foto_wajib" value="1">
                    <label class="form-check-label" for="edit-foto-wajib">Foto wajib saat pendaftaran</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="edit-pendaftaran-buka" name="pendaftaran_buka" value="1">
                    <label class="form-check-label" for="edit-pendaftaran-buka">Pendaftaran dibuka</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(btn) {
    var d = btn.dataset;
    document.getElementById('editForm').action = '/admin/cabangs/' + d.id;
    document.getElementById('edit-nama').value = d.nama;
    document.getElementById('edit-alamat').value = d.alamat || '';
    document.getElementById('edit-kontak').value = d.kontak || '';
    document.getElementById('edit-whatsapp-link').value = d.waLink || '';
    document.getElementById('edit-foto-wajib').checked = d.fotoWajib === '1';
    document.getElementById('edit-pendaftaran-buka').checked = d.pendaftaranBuka === '1';
    document.getElementById('edit-kelas-min').value = d.kelasMin || '';
    document.getElementById('edit-kelas-max').value = d.kelasMax || '';
    var selected = [];
    try { selected = JSON.parse(d.mataPelajaran || '[]'); } catch(e) {}
    document.querySelectorAll('.edit-mp-check').forEach(function(cb) {
        cb.checked = selected.indexOf(cb.value) !== -1;
    });
    $('#editModal').modal('show');
}
</script>
@endpush
