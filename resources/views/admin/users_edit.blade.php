@extends('layouts.admin')

@section('page-title', 'Edit Pengguna')

@section('content')
@php
    $userRoleNames = $user->roles->pluck('name')->all();
    $student = $user->studentProfile;
    $mentor  = $user->mentorProfile;
    $adminP  = $user->adminProfile;
@endphp
<style>
.uf-section{border-top:1px solid #e9ecef;margin-top:1.25rem;padding-top:1rem}
.uf-section-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#adb5bd;margin-bottom:.75rem}
.role-pills{display:flex;flex-wrap:wrap;gap:.4rem}
.role-pill input{position:absolute;opacity:0;pointer-events:none}
.role-pill label{padding:.3rem .85rem;border-radius:999px;border:1.5px solid #ced4da;font-size:.78rem;font-weight:600;cursor:pointer;margin:0;user-select:none;color:#495057;transition:background .12s,border-color .12s}
.role-pill input:checked + label{background:#1971c2;border-color:#1971c2;color:#fff}
.form-label{font-size:.78rem;font-weight:600;margin-bottom:.25rem;color:#495057}
.form-actions{display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.5rem;flex-wrap:wrap}
.form-actions .btn{flex:1 1 auto}
@media(min-width:480px){.form-actions .btn{flex:0 0 auto}}
</style>

<div class="card">
    <div class="card-body">
        {{-- User header --}}
        <div class="d-flex align-items-center mb-3 gap-2">
            <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=80&background=1e3a5f&color=fff' }}"
                 class="img-circle mr-2" style="width:48px;height:48px;object-fit:cover" alt="">
            <div>
                <strong style="font-size:.9rem">{{ $user->name }}</strong>
                <div class="text-muted" style="font-size:.75rem">@{{ $user->username }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data" id="userForm">
            @csrf
            @method('PUT')

            {{-- Akun Login --}}
            <div class="uf-section-label">Akun Login</div>
            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="form-group col-12 col-md-3">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required pattern="[a-z0-9\-]+">
                    <small class="text-muted">huruf kecil, angka, strip</small>
                </div>
                <div class="form-group col-12 col-md-3">
                    <label class="form-label">Password Baru</label>
                    <input type="text" name="password" class="form-control" placeholder="kosong = tidak diubah">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-12 col-md-5">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                </div>
                <div class="form-group col-12 col-md-4">
                    <label class="form-label">Cabang</label>
                    <select name="cabang_id" class="form-control">
                        <option value="">- Tidak ada -</option>
                        @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" {{ old('cabang_id', $user->cabang_id) == $cabang->id ? 'selected' : '' }}>
                            {{ $cabang->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-md-3 d-flex align-items-end pb-1">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="is_active" value="1" id="isActive"
                               class="custom-control-input" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="isActive">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Foto Profil</label>
                <input type="file" name="avatar" class="form-control-file" accept="image/*">
            </div>

            {{-- Role --}}
            <div class="uf-section">
                <div class="uf-section-label">Role</div>
                @php $oldRoles = old('role_names', $userRoleNames); @endphp
                <div class="role-pills">
                    @foreach($roles as $role)
                    <div class="role-pill">
                        <input type="checkbox" name="role_names[]" value="{{ $role->name }}"
                               id="role-{{ $role->name }}" class="role-toggle"
                               {{ in_array($role->name, $oldRoles) ? 'checked' : '' }}>
                        <label for="role-{{ $role->name }}">{{ ucfirst(str_replace('_',' ',$role->name)) }}</label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Profil Siswa --}}
            <div class="profile-section uf-section" data-role="student">
                <div class="uf-section-label">Profil Siswa</div>
                <div class="form-row">
                    <div class="form-group col-6 col-md-3">
                        <label class="form-label">Nomor Siswa</label>
                        <input type="text" name="student[student_number]" class="form-control" value="{{ old('student.student_number', $student?->student_number) }}">
                    </div>
                    <div class="form-group col-6 col-md-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="student[gender]" class="form-control">
                            @php $g = old('student.gender', $student?->gender); @endphp
                            <option value="">-</option>
                            <option value="L" {{ $g === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ $g === 'P' ? 'selected' : '' }}>Perempuan</option>
                            <option value="Lainnya" {{ $g === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group col-6 col-md-3">
                        <label class="form-label">Kelas</label>
                        <input type="text" name="student[grade_class]" class="form-control" value="{{ old('student.grade_class', $student?->grade_class) }}" placeholder="X-A">
                    </div>
                    <div class="form-group col-6 col-md-3">
                        <label class="form-label">Tahun Masuk</label>
                        <input type="number" name="student[entry_year]" class="form-control" min="2000" max="2100" value="{{ old('student.entry_year', $student?->entry_year) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="student[birth_place]" class="form-control" value="{{ old('student.birth_place', $student?->birth_place) }}">
                    </div>
                    <div class="form-group col-12 col-md-6">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="student[birth_date]" class="form-control" value="{{ old('student.birth_date', optional($student?->birth_date)->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-5">
                        <label class="form-label">Nama Wali</label>
                        <input type="text" name="student[guardian_name]" class="form-control" value="{{ old('student.guardian_name', $student?->guardian_name) }}">
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label class="form-label">No HP Wali</label>
                        <input type="text" name="student[guardian_phone]" class="form-control" value="{{ old('student.guardian_phone', $student?->guardian_phone) }}">
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label class="form-label">Nama Sekolah</label>
                        <input type="text" name="student[school_name]" class="form-control" value="{{ old('student.school_name', $student?->school_name) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <textarea name="student[address]" class="form-control" rows="2">{{ old('student.address', $student?->address) }}</textarea>
                </div>
            </div>

            {{-- Profil Mentor --}}
            <div class="profile-section uf-section" data-role="mentor">
                <div class="uf-section-label">Profil Mentor</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-4">
                        <label class="form-label">Bidang Keahlian</label>
                        <input type="text" name="mentor[expertise]" class="form-control" value="{{ old('mentor.expertise', $mentor?->expertise) }}">
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label class="form-label">Pendidikan</label>
                        <input type="text" name="mentor[education]" class="form-control" value="{{ old('mentor.education', $mentor?->education) }}">
                    </div>
                    <div class="form-group col-6 col-md-2">
                        <label class="form-label">Pengalaman (thn)</label>
                        <input type="number" name="mentor[experience_years]" class="form-control" min="0" max="80" value="{{ old('mentor.experience_years', $mentor?->experience_years) }}">
                    </div>
                    <div class="form-group col-6 col-md-2">
                        <label class="form-label">Tarif/Jam</label>
                        <input type="number" step="0.01" name="mentor[hourly_rate]" class="form-control" value="{{ old('mentor.hourly_rate', $mentor?->hourly_rate) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="mentor[bio]" class="form-control" rows="2">{{ old('mentor.bio', $mentor?->bio) }}</textarea>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="mentor[is_available]" value="1" id="mentorAvail"
                           class="custom-control-input" {{ old('mentor.is_available', $mentor?->is_available ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="mentorAvail">Tersedia</label>
                </div>
            </div>

            {{-- Profil Admin --}}
            <div class="profile-section uf-section" data-role="admin">
                <div class="uf-section-label">Profil Admin</div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-4">
                        <label class="form-label">Nomor Pegawai</label>
                        <input type="text" name="admin[employee_number]" class="form-control" value="{{ old('admin.employee_number', $adminP?->employee_number) }}">
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label class="form-label">Departemen</label>
                        <input type="text" name="admin[department]" class="form-control" value="{{ old('admin.department', $adminP?->department) }}">
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="admin[position]" class="form-control" value="{{ old('admin.position', $adminP?->position) }}">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.users') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@if(in_array('student', $userRoleNames) || in_array('scholarship_teenager', $userRoleNames))
<div class="card mt-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-qrcode mr-1"></i> QR Code Absensi</h6></div>
    <div class="card-body text-center">
        <p class="text-muted small mb-3">ID siswa: <strong>{{ $user->id }}</strong></p>
        {!! QrCode::size(200)->generate($user->id) !!}
        <div class="mt-3">
            <a href="{{ route('admin.users.qr-print', $user->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-print mr-1"></i> Cetak Kartu QR
            </a>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function syncProfileSections() {
    var checked = Array.prototype.map.call(
        document.querySelectorAll('.role-toggle:checked'), function(el){ return el.value; }
    );
    if (checked.indexOf('scholarship_teenager') !== -1 && checked.indexOf('student') === -1) {
        checked.push('student');
    }
    document.querySelectorAll('.profile-section').forEach(function(sec){
        sec.style.display = checked.indexOf(sec.dataset.role) !== -1 ? '' : 'none';
    });
}
document.querySelectorAll('.role-toggle').forEach(function(el){
    el.addEventListener('change', syncProfileSections);
});
syncProfileSections();
</script>
@endpush
@endsection
