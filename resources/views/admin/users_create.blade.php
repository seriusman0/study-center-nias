@extends('layouts.admin')

@section('page-title', 'Tambah Pengguna')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" id="userForm">
            @csrf

            <div class="alert alert-info" style="font-size:13px">
                <i class="fas fa-info-circle mr-1"></i>
                Username dibuat otomatis dari nama. Password default <strong>12345</strong> jika kosong.
                Pilih satu atau lebih role; bagian profil akan menyesuaikan.
            </div>

            <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Akun Login</h6>

            <div class="form-row">
                <div class="form-group col-md-8">
                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Password</label>
                    <input type="text" name="password" class="form-control" placeholder="default: 12345">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="form-group col-md-3">
                    <label>Cabang</label>
                    <select name="cabang_id" class="form-control">
                        <option value="">- Tidak ada -</option>
                        @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" {{ old('cabang_id') == $cabang->id ? 'selected' : '' }}>
                            {{ $cabang->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Foto Profil</label>
                    <input type="file" name="avatar" class="form-control-file" accept="image/*">
                </div>
            </div>

            <h6 class="text-uppercase text-muted small font-weight-bold mb-2 mt-3">Role</h6>
            <div class="form-group">
                @php $oldRoles = old('role_names', ['student']); @endphp
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

            {{-- Student profile --}}
            <div class="profile-section" data-role="student">
                <h6 class="text-uppercase text-muted small font-weight-bold mb-3 mt-3">Profil Siswa</h6>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Nomor Siswa</label>
                        <input type="text" name="student[student_number]" class="form-control" value="{{ old('student.student_number') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tempat Lahir</label>
                        <input type="text" name="student[birth_place]" class="form-control" value="{{ old('student.birth_place') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="student[birth_date]" class="form-control" value="{{ old('student.birth_date') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Jenis Kelamin</label>
                        <select name="student[gender]" class="form-control">
                            <option value="">-</option>
                            <option value="L" {{ old('student.gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('student.gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                            <option value="Lainnya" {{ old('student.gender') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Nama Wali</label>
                        <input type="text" name="student[guardian_name]" class="form-control" value="{{ old('student.guardian_name') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>No HP Wali</label>
                        <input type="text" name="student[guardian_phone]" class="form-control" value="{{ old('student.guardian_phone') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Nama Sekolah</label>
                        <input type="text" name="student[school_name]" class="form-control" value="{{ old('student.school_name') }}">
                    </div>
                    <div class="form-group col-md-1">
                        <label>Kelas</label>
                        <input type="text" name="student[grade_class]" class="form-control" value="{{ old('student.grade_class') }}" placeholder="X-A">
                    </div>
                    <div class="form-group col-md-1">
                        <label>Thn Masuk</label>
                        <input type="number" name="student[entry_year]" class="form-control" min="2000" max="2100" value="{{ old('student.entry_year') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="student[address]" class="form-control" rows="2">{{ old('student.address') }}</textarea>
                </div>
            </div>

            {{-- Mentor profile --}}
            <div class="profile-section" data-role="mentor">
                <h6 class="text-uppercase text-muted small font-weight-bold mb-3 mt-3">Profil Mentor</h6>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Bidang Keahlian</label>
                        <input type="text" name="mentor[expertise]" class="form-control" value="{{ old('mentor.expertise') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Pendidikan</label>
                        <input type="text" name="mentor[education]" class="form-control" value="{{ old('mentor.education') }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Pengalaman (thn)</label>
                        <input type="number" name="mentor[experience_years]" class="form-control" min="0" max="80" value="{{ old('mentor.experience_years') }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Tarif/Jam</label>
                        <input type="number" step="0.01" name="mentor[hourly_rate]" class="form-control" value="{{ old('mentor.hourly_rate') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Bio Mentor</label>
                    <textarea name="mentor[bio]" class="form-control" rows="2">{{ old('mentor.bio') }}</textarea>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="mentor[is_available]" value="1" id="mentorAvail"
                           class="custom-control-input" {{ old('mentor.is_available', '1') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="mentorAvail">Tersedia</label>
                </div>
            </div>

            {{-- Admin profile --}}
            <div class="profile-section" data-role="admin">
                <h6 class="text-uppercase text-muted small font-weight-bold mb-3 mt-3">Profil Admin</h6>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Nomor Pegawai</label>
                        <input type="text" name="admin[employee_number]" class="form-control" value="{{ old('admin.employee_number') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Bagian/Departemen</label>
                        <input type="text" name="admin[department]" class="form-control" value="{{ old('admin.department') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Jabatan</label>
                        <input type="text" name="admin[position]" class="form-control" value="{{ old('admin.position') }}">
                    </div>
                </div>
            </div>

            <div class="text-right mt-4">
                <a href="{{ route('admin.users') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
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
