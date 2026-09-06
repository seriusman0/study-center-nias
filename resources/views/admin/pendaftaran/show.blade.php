@extends('layouts.admin')

@section('title', 'Detail Pendaftaran — ' . $user->name)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detail Pendaftaran</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.pendaftaran.index') }}">Pendaftaran</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">

            {{-- Data Pendaftar --}}
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user mr-2"></i>Data Pendaftar</h3>
                    </div>
                    <div class="card-body">
                        @php $profile = $user->studentProfile; @endphp

                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width:40%">Nama Lengkap</td>
                                <td><strong>{{ $user->name }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Username</td>
                                <td>{{ $user->username }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jenis Kelamin</td>
                                <td>{{ $profile->gender === 'laki-laki' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Lahir</td>
                                <td>{{ $profile->birth_date ? $profile->birth_date->format('d F Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Sekolah</td>
                                <td>{{ $profile->school_name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kelas</td>
                                <td>{{ $profile->grade_class }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Alamat</td>
                                <td>{{ $profile->address }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">HP Orangtua/Wali</td>
                                <td>
                                    {{ $profile->guardian_phone }}
                                    @if($profile->guardian_whatsapp)
                                    <a href="{{ $profile->guardian_whatsapp }}" target="_blank" rel="noopener noreferrer"
                                       class="btn btn-xs btn-success ml-2" style="font-size:11px;padding:2px 8px">
                                        <i class="fab fa-whatsapp"></i> WhatsApp
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">HP Siswa</td>
                                <td>
                                    @if($profile->student_phone)
                                        {{ $profile->student_phone }}
                                        @if($profile->student_whatsapp)
                                        <a href="{{ $profile->student_whatsapp }}" target="_blank" rel="noopener noreferrer"
                                           class="btn btn-xs btn-success ml-2" style="font-size:11px;padding:2px 8px">
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </a>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Mata Pelajaran</td>
                                <td>
                                    @if(!empty($profile->mata_pelajaran))
                                        @foreach($profile->mata_pelajaran as $mp)
                                            <span class="badge badge-info mr-1 mb-1">{{ $mp }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Catatan Jadwal</td>
                                <td>
                                    @if($profile->note)
                                        <em>{{ $profile->note }}</em>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cabang</td>
                                <td>{{ $user->cabang?->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Daftar</td>
                                <td>{{ $user->created_at->format('d F Y, H:i') }} WIB</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Catatan Admin Sebelumnya --}}
                @if($profile->catatan_admin)
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-sticky-note mr-2"></i>Catatan Admin Sebelumnya</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $profile->catatan_admin }}</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Foto & Validasi --}}
            <div class="col-md-5">

                {{-- Foto --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-image mr-2"></i>Foto Pendaftar</h3>
                    </div>
                    <div class="card-body text-center">
                        @if($profile->photo)
                        <img src="{{ asset('storage/' . $profile->photo) }}"
                             alt="Foto {{ $user->name }}"
                             class="img-fluid rounded"
                             style="max-height:300px;object-fit:contain;border:1px solid #dee2e6">
                        @else
                        <p class="text-muted">Tidak ada foto.</p>
                        @endif
                    </div>
                </div>

                {{-- Status saat ini --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Status Saat Ini</h3>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $st = $profile->status ?? 'pending';
                            $badge = match($st) {
                                'diterima'  => 'success',
                                'ditolak'   => 'danger',
                                'perbaikan' => 'warning',
                                default     => 'secondary',
                            };
                            $label = match($st) {
                                'diterima'  => 'Diterima',
                                'ditolak'   => 'Ditolak',
                                'perbaikan' => 'Perlu Perbaikan',
                                default     => 'Menunggu Validasi',
                            };
                        @endphp
                        <span class="badge badge-{{ $badge }}" style="font-size:14px;padding:6px 16px">{{ $label }}</span>
                        @if($st === 'diterima')
                        <p class="text-muted small mt-2 mb-0">Akun sudah aktif, siswa dapat login.</p>
                        @php
                            $userRole = $user->roles()->first()?->name;
                            $quickSetupRoute = match($userRole) {
                                'college'              => 'admin.jurnal-college.quick-setup',
                                'scholarship_teenager' => 'admin.jurnal-scholarship-teenager.quick-setup',
                                default                => null,
                            };
                        @endphp
                        @if($quickSetupRoute)
                        <a href="{{ route($quickSetupRoute, $user) }}"
                           class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-cog mr-1"></i> Setup Jurnal
                        </a>
                        @elseif($userRole === 'student')
                        <a href="{{ route('admin.jurnal.life-items.student', $user) }}"
                           class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-cog mr-1"></i> Setup Jurnal
                        </a>
                        @endif
                        @elseif($st === 'ditolak')
                        <p class="text-muted small mt-2 mb-0">Akun tidak diaktifkan.</p>
                        @elseif($st === 'perbaikan')
                        <p class="text-muted small mt-2 mb-0">Menunggu perbaikan data dari pendaftar.</p>
                        @endif
                    </div>
                </div>

                {{-- Link Cek Status --}}
                @php
                    $cekStatusUrl = route('pendaftaran.cek', $user->username);
                    $waPhone = $profile->student_phone ?? $profile->guardian_phone ?? null;
                    if ($waPhone) {
                        $waPhone = '62' . ltrim(preg_replace('/\D/', '', $waPhone), '0');
                        $waMsg = 'Halo ' . $user->name . ', berikut link untuk cek status pendaftaran kamu: ' . $cekStatusUrl;
                        $waUrl = 'https://wa.me/' . $waPhone . '?text=' . urlencode($waMsg);
                    }
                @endphp
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-link mr-2"></i>Link Cek Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="input-group mb-2">
                            <input type="text" id="cekStatusUrl" class="form-control form-control-sm"
                                   value="{{ $cekStatusUrl }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                        onclick="copyStatusLink()" id="btnCopy">
                                    <i class="fas fa-copy"></i> Salin
                                </button>
                            </div>
                        </div>
                        @if(!empty($waUrl))
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                           class="btn btn-success btn-sm btn-block">
                            <i class="fab fa-whatsapp mr-1"></i> Kirim via WhatsApp
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Link Perbaikan --}}
                @if($profile->status === 'perbaikan')
                @php
                    $hasValidToken = $profile->update_token && $profile->update_token_expires_at && $profile->update_token_expires_at->isFuture();
                    $updateUrl = $hasValidToken ? route('pendaftaran.update.form', $profile->update_token) : null;
                @endphp
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Link Perbaikan Data</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-muted mb-3">Kirim link ini ke pendaftar agar mereka dapat memperbarui data (termasuk foto). Link berlaku 7 hari.</p>

                        <div id="updateLinkSection" class="{{ $hasValidToken ? '' : 'd-none' }}">
                            <div class="input-group mb-2">
                                <input type="text" id="updateLinkUrl" class="form-control form-control-sm"
                                       value="{{ $updateUrl }}" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-outline-secondary" type="button"
                                            onclick="copyUpdateLink()" id="btnCopyUpdate">
                                        <i class="fas fa-copy"></i> Salin
                                    </button>
                                </div>
                            </div>
                            @if($hasValidToken)
                            <p class="text-xs text-muted mb-2">Berlaku hingga: {{ $profile->update_token_expires_at->translatedFormat('d M Y, H:i') }}</p>
                            @php
                                $waUpdatePhone = $profile->student_phone ?? $profile->guardian_phone ?? null;
                                $waUpdateUrl = null;
                                if ($waUpdatePhone) {
                                    $waUpdatePhoneNum = '62' . ltrim(preg_replace('/\D/', '', $waUpdatePhone), '0');
                                    $waUpdateMsg = 'Halo ' . $user->name . ', pengurus meminta Anda untuk memperbarui data pendaftaran melalui link berikut: ' . $updateUrl . ' (Link berlaku 7 hari)';
                                    $waUpdateUrl = 'https://wa.me/' . $waUpdatePhoneNum . '?text=' . urlencode($waUpdateMsg);
                                }
                            @endphp
                            @if($waUpdateUrl)
                            <a href="{{ $waUpdateUrl }}" target="_blank" rel="noopener noreferrer"
                               class="btn btn-success btn-sm btn-block">
                                <i class="fab fa-whatsapp mr-1"></i> Kirim via WhatsApp
                            </a>
                            @endif
                            @endif
                        </div>

                        <button type="button" id="btnGenerateLink" onclick="generateUpdateLink()"
                                class="btn btn-warning btn-sm btn-block {{ $hasValidToken ? 'mt-2' : '' }}">
                            <i class="fas fa-redo mr-1"></i> {{ $hasValidToken ? 'Generate Ulang Link' : 'Generate Link Perbaikan' }}
                        </button>
                        <div id="generateError" class="text-danger text-sm mt-2 d-none"></div>
                    </div>
                </div>
                @endif

                {{-- Form Validasi --}}
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-check-circle mr-2"></i>Proses Validasi</h3>
                    </div>
                    <form method="POST" action="{{ route('admin.pendaftaran.validasi', $user) }}">
                        @csrf
                        @method('PATCH')
                        <div class="card-body">

                            @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $err)
                                    <div>{{ $err }}</div>
                                @endforeach
                            </div>
                            @endif

                            <div class="form-group">
                                <label class="font-weight-bold">Keputusan <span class="text-danger">*</span></label>
                                <div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="st_diterima" name="status" value="diterima"
                                               class="custom-control-input"
                                               {{ old('status', $profile->status) === 'diterima' ? 'checked' : '' }}>
                                        <label class="custom-control-label text-success font-weight-bold" for="st_diterima">
                                            <i class="fas fa-check-circle"></i> Terima — Aktifkan akun siswa
                                        </label>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="st_perbaikan" name="status" value="perbaikan"
                                               class="custom-control-input"
                                               {{ old('status', $profile->status) === 'perbaikan' ? 'checked' : '' }}>
                                        <label class="custom-control-label text-warning font-weight-bold" for="st_perbaikan">
                                            <i class="fas fa-exclamation-circle"></i> Perlu Perbaikan — Data belum lengkap/valid
                                        </label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="st_ditolak" name="status" value="ditolak"
                                               class="custom-control-input"
                                               {{ old('status', $profile->status) === 'ditolak' ? 'checked' : '' }}>
                                        <label class="custom-control-label text-danger font-weight-bold" for="st_ditolak">
                                            <i class="fas fa-times-circle"></i> Tolak — Pendaftaran ditolak
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Role picker — muncul hanya saat "Diterima" dipilih --}}
                            <div class="form-group" id="rolePickerGroup" style="{{ old('status', $profile->status) === 'diterima' ? '' : 'display:none' }}">
                                <label class="font-weight-bold">Role Akun <span class="text-danger">*</span></label>
                                <select name="target_role" class="form-control">
                                    <option value="">— Pilih role —</option>
                                    <option value="student" {{ old('target_role', 'student') === 'student' ? 'selected' : '' }}>
                                        Siswa (student)
                                    </option>
                                    <option value="scholarship_teenager" {{ old('target_role') === 'scholarship_teenager' ? 'selected' : '' }}>
                                        Remaja Beasiswa (scholarship_teenager)
                                    </option>
                                    <option value="college" {{ old('target_role') === 'college' ? 'selected' : '' }}>
                                        Mahasiswa (college)
                                    </option>
                                </select>
                                <small class="text-muted">Role ini menentukan jenis jurnal dan item jurnal default yang di-assign.</small>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Cabang</label>
                                <select name="cabang_id" class="form-control">
                                    <option value="">— Tidak ubah cabang —</option>
                                    @foreach($cabangs as $c)
                                    <option value="{{ $c->id }}" {{ $user->cabang_id == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Ubah hanya jika pendaftar mendaftar ke cabang yang salah.</small>
                            </div>

                            <div class="form-group">
                                <label for="catatan_admin">Catatan untuk Pendaftar <small class="text-muted">(opsional, disarankan jika tolak/perbaikan)</small></label>
                                <textarea name="catatan_admin" id="catatan_admin" rows="4"
                                          class="form-control" maxlength="1000"
                                          placeholder="Contoh: Foto tidak jelas, mohon kirim ulang foto dengan pencahayaan yang lebih baik.">{{ old('catatan_admin', $profile->catatan_admin) }}</textarea>
                                <small class="text-muted">Maksimal 1000 karakter.</small>
                            </div>

                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i> Simpan Keputusan
                            </button>
                        </div>
                    </form>
                </div>

                <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-secondary btn-block mb-3">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                </a>

            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Show/hide role picker based on status selection
document.querySelectorAll('input[name="status"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var group = document.getElementById('rolePickerGroup');
        if (group) {
            group.style.display = this.value === 'diterima' ? '' : 'none';
        }
    });
});

function copyStatusLink() {
    var input = document.getElementById('cekStatusUrl');
    var btn = document.getElementById('btnCopy');
    navigator.clipboard.writeText(input.value).then(function() {
        btn.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
        btn.classList.replace('btn-outline-secondary', 'btn-success');
        setTimeout(function() {
            btn.innerHTML = '<i class="fas fa-copy"></i> Salin';
            btn.classList.replace('btn-success', 'btn-outline-secondary');
        }, 2000);
    });
}

function copyUpdateLink() {
    var input = document.getElementById('updateLinkUrl');
    var btn = document.getElementById('btnCopyUpdate');
    navigator.clipboard.writeText(input.value).then(function() {
        btn.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
        btn.classList.replace('btn-outline-secondary', 'btn-success');
        setTimeout(function() {
            btn.innerHTML = '<i class="fas fa-copy"></i> Salin';
            btn.classList.replace('btn-success', 'btn-outline-secondary');
        }, 2000);
    });
}

function generateUpdateLink() {
    var btn = document.getElementById('btnGenerateLink');
    var errEl = document.getElementById('generateError');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
    if (errEl) errEl.classList.add('d-none');

    fetch('{{ route('admin.pendaftaran.generate-update-link', $user, false) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(function(r) {
        if (!r.ok) {
            return r.text().then(function(text) {
                throw new Error('HTTP ' + r.status + ': ' + text.substring(0, 200));
            });
        }
        return r.json();
    })
    .then(function(data) {
        var section = document.getElementById('updateLinkSection');
        var urlInput = document.getElementById('updateLinkUrl');
        if (section && urlInput) {
            urlInput.value = data.url;
            section.classList.remove('d-none');
        }
        btn.innerHTML = '<i class="fas fa-redo mr-1"></i> Generate Ulang Link';
        btn.disabled = false;
    })
    .catch(function(err) {
        if (errEl) {
            errEl.textContent = 'Gagal generate link: ' + (err.message || 'Coba lagi.');
            errEl.classList.remove('d-none');
        }
        btn.innerHTML = '<i class="fas fa-redo mr-1"></i> Generate Link Perbaikan';
        btn.disabled = false;
        console.error('generateUpdateLink error:', err);
    });
}
</script>
@endpush
