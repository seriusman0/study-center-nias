@extends('layouts.admin')

@section('page-title', 'Edit Pengguna')

@section('content')
@php
    $userRoleNames = $user->roles->pluck('name')->all();
    $student = $user->studentProfile;
    $mentor  = $user->mentorProfile;
    $adminP  = $user->adminProfile;
@endphp
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data" id="userForm">
            @csrf
            @method('PUT')

            <div class="form-row align-items-center mb-3">
                <div class="col-auto">
                    <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=80&background=1e3a5f&color=fff' }}"
                         class="img-circle" style="width:64px;height:64px;object-fit:cover" alt="">
                </div>
                <div class="col">
                    <strong>{{ $user->name }}</strong><br>
                    <small class="text-muted">@{{ $user->username }}</small>
                </div>
            </div>

            <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Akun Login</h6>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required pattern="[a-z0-9]+">
                    <small class="text-muted">huruf kecil &amp; angka</small>
                </div>
                <div class="form-group col-md-3">
                    <label>Password Baru</label>
                    <input type="text" name="password" class="form-control" placeholder="kosong = tidak diubah">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                </div>
                <div class="form-group col-md-3">
                    <label>Cabang</label>
                    <select name="cabang_id" class="form-control">
                        <option value="">- Tidak ada -</option>
                        @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" {{ old('cabang_id', $user->cabang_id) == $cabang->id ? 'selected' : '' }}>
                            {{ $cabang->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="is_active" value="1" id="isActive"
                               class="custom-control-input" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="isActive">Aktif</label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label>Foto Profil</label>
                    <input type="file" name="avatar" class="form-control-file" accept="image/*">
                </div>
            </div>

            <h6 class="text-uppercase text-muted small font-weight-bold mb-2 mt-3">Role</h6>
            <div class="form-group">
                @php $oldRoles = old('role_names', $userRoleNames); @endphp
                @foreach($roles as $role)
                <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox" name="role_names[]" value="{{ $role->name }}"
                           id="role-{{ $role->name }}"
                           class="custom-control-input role-toggle"
                           {{ in_array($role->name, $oldRoles) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="role-{{ $role->name }}">{{ ucfirst($role->name) }}</label>
                </div>
                @endforeach
            </div>

            {{-- Student --}}
            <div class="profile-section" data-role="student">
                <h6 class="text-uppercase text-muted small font-weight-bold mb-3 mt-3">Profil Siswa</h6>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Nomor Siswa</label>
                        <input type="text" name="student[student_number]" class="form-control" value="{{ old('student.student_number', $student?->student_number) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tempat Lahir</label>
                        <input type="text" name="student[birth_place]" class="form-control" value="{{ old('student.birth_place', $student?->birth_place) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="student[birth_date]" class="form-control" value="{{ old('student.birth_date', optional($student?->birth_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Jenis Kelamin</label>
                        <select name="student[gender]" class="form-control">
                            @php $g = old('student.gender', $student?->gender); @endphp
                            <option value="">-</option>
                            <option value="L" {{ $g === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ $g === 'P' ? 'selected' : '' }}>Perempuan</option>
                            <option value="Lainnya" {{ $g === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Nama Wali</label>
                        <input type="text" name="student[guardian_name]" class="form-control" value="{{ old('student.guardian_name', $student?->guardian_name) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>No HP Wali</label>
                        <input type="text" name="student[guardian_phone]" class="form-control" value="{{ old('student.guardian_phone', $student?->guardian_phone) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Nama Sekolah</label>
                        <input type="text" name="student[school_name]" class="form-control" value="{{ old('student.school_name', $student?->school_name) }}">
                    </div>
                    <div class="form-group col-md-1">
                        <label>Kelas</label>
                        <input type="text" name="student[grade_class]" class="form-control" value="{{ old('student.grade_class', $student?->grade_class) }}" placeholder="X-A">
                    </div>
                    <div class="form-group col-md-1">
                        <label>Thn Masuk</label>
                        <input type="number" name="student[entry_year]" class="form-control" min="2000" max="2100" value="{{ old('student.entry_year', $student?->entry_year) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="student[address]" class="form-control" rows="2">{{ old('student.address', $student?->address) }}</textarea>
                </div>
            </div>

            {{-- Mentor --}}
            <div class="profile-section" data-role="mentor">
                <h6 class="text-uppercase text-muted small font-weight-bold mb-3 mt-3">Profil Mentor</h6>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Bidang Keahlian</label>
                        <input type="text" name="mentor[expertise]" class="form-control" value="{{ old('mentor.expertise', $mentor?->expertise) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Pendidikan</label>
                        <input type="text" name="mentor[education]" class="form-control" value="{{ old('mentor.education', $mentor?->education) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Pengalaman (thn)</label>
                        <input type="number" name="mentor[experience_years]" class="form-control" min="0" max="80" value="{{ old('mentor.experience_years', $mentor?->experience_years) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Tarif/Jam</label>
                        <input type="number" step="0.01" name="mentor[hourly_rate]" class="form-control" value="{{ old('mentor.hourly_rate', $mentor?->hourly_rate) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Bio Mentor</label>
                    <textarea name="mentor[bio]" class="form-control" rows="2">{{ old('mentor.bio', $mentor?->bio) }}</textarea>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="mentor[is_available]" value="1" id="mentorAvail"
                           class="custom-control-input" {{ old('mentor.is_available', $mentor?->is_available ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="mentorAvail">Tersedia</label>
                </div>
            </div>

            {{-- Admin --}}
            <div class="profile-section" data-role="admin">
                <h6 class="text-uppercase text-muted small font-weight-bold mb-3 mt-3">Profil Admin</h6>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Nomor Pegawai</label>
                        <input type="text" name="admin[employee_number]" class="form-control" value="{{ old('admin.employee_number', $adminP?->employee_number) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Bagian/Departemen</label>
                        <input type="text" name="admin[department]" class="form-control" value="{{ old('admin.department', $adminP?->department) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Jabatan</label>
                        <input type="text" name="admin[position]" class="form-control" value="{{ old('admin.position', $adminP?->position) }}">
                    </div>
                </div>
            </div>

            <div class="text-right mt-4">
                <a href="{{ route('admin.users') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function syncProfileSections() {
    var checked = Array.prototype.map.call(
        document.querySelectorAll('.role-toggle:checked'), function(el){ return el.value; }
    );
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
