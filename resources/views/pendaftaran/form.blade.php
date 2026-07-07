@extends('layouts.app')

@section('title', 'Pendaftaran Siswa - Study Center Nias')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-sc-teal-800">Pendaftaran Siswa Baru</h1>
            <p class="text-sc-ink-500 mt-2">Isi form berikut dengan lengkap dan benar. Pengurus akan memvalidasi data Anda sebelum akun diaktifkan.</p>
        </div>

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 mb-6 text-sm">
            <p class="font-semibold mb-1">Terdapat kesalahan pada form:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-sc-line p-8">
            <form method="POST" action="{{ route('pendaftaran.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- NAMA LENGKAP --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $prevData['name'] ?? '') }}"
                           autocapitalize="words" autocomplete="name"
                           placeholder="Contoh: Budi Santoso"
                           class="w-full border @error('name') border-red-400 @else border-sc-line @enderror rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- JENIS KELAMIN --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="gender" value="laki-laki"
                                   {{ old('gender', $prevData['gender'] ?? '') === 'laki-laki' ? 'checked' : '' }}
                                   class="accent-sc-teal-600 w-4 h-4">
                            <span class="text-sm">Laki-laki</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="gender" value="perempuan"
                                   {{ old('gender', $prevData['gender'] ?? '') === 'perempuan' ? 'checked' : '' }}
                                   class="accent-sc-teal-600 w-4 h-4">
                            <span class="text-sm">Perempuan</span>
                        </label>
                    </div>
                    @error('gender')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NOMOR HP SISWA --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Nomor HP Siswa <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 border @error('student_phone') border-red-400 @else border-sc-line @enderror border-r-0 rounded-l-xl bg-gray-50 text-sm text-gray-600 font-medium select-none">+62</span>
                        <input type="tel" name="student_phone" value="{{ old('student_phone', isset($prevData['student_phone']) ? ltrim($prevData['student_phone'], '+62') : '') }}"
                               inputmode="numeric" pattern="[0-9]*" maxlength="13"
                               placeholder="81234567890"
                               class="flex-1 border @error('student_phone') border-red-400 @else border-sc-line @enderror rounded-r-xl px-4 py-3 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">
                    </div>
                    <p class="text-gray-400 text-xs mt-1">Masukkan angka setelah 0, tanpa angka 0 di depan. Contoh: 81234567890</p>
                    @error('student_phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NAMA SEKOLAH --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Nama Sekolah <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="school_name" name="school_name" value="{{ old('school_name', $prevData['school_name'] ?? '') }}"
                           autocomplete="organization" placeholder="Contoh: SMA NEGERI 1 GUNUNGSITOLI"
                           class="w-full border @error('school_name') border-red-400 @else border-sc-line @enderror rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20"
                           style="text-transform:uppercase">
                    @error('school_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- KELAS --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Kelas <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="grade_class" name="grade_class" value="{{ old('grade_class', $prevData['grade_class'] ?? '') }}"
                           inputmode="numeric" min="1" max="12" placeholder="Contoh: 7 (SMP), 10 (SMA/SMK), 3 (SD)"
                           class="w-full border @error('grade_class') border-red-400 @else border-sc-line @enderror rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">
                    <p class="text-gray-400 text-xs mt-1">SD: 1–6 &bull; SMP: 7–9 &bull; SMA/SMK: 10–12</p>
                    @error('grade_class')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- TANGGAL LAHIR --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Tanggal Lahir <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $prevData['birth_date'] ?? '') }}"
                           max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                           class="w-full border @error('birth_date') border-red-400 @else border-sc-line @enderror rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">
                    @error('birth_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ALAMAT --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Alamat Rumah Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <textarea id="address" name="address" rows="3"
                              autocapitalize="sentences"
                              placeholder="Contoh: Jl. Hiligemötö No. 12, Kelurahan Ilir, Gunungsitoli"
                              class="w-full border @error('address') border-red-400 @else border-sc-line @enderror rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20 resize-none">{{ old('address', $prevData['address'] ?? '') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NO HP ORANGTUA --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        No HP Orangtua / Wali <span class="text-red-500">*</span>
                    </label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 border @error('guardian_phone') border-red-400 @else border-sc-line @enderror border-r-0 rounded-l-xl bg-gray-50 text-sm text-gray-600 font-medium select-none">+62</span>
                        <input type="tel" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', isset($prevData['guardian_phone']) ? ltrim($prevData['guardian_phone'], '+62') : '') }}"
                               inputmode="numeric" pattern="[0-9]*" maxlength="13"
                               placeholder="81234567890"
                               class="flex-1 border @error('guardian_phone') border-red-400 @else border-sc-line @enderror rounded-r-xl px-4 py-3 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20">
                    </div>
                    <p class="text-gray-400 text-xs mt-1">Masukkan angka setelah 0, tanpa angka 0 di depan. Contoh: 81234567890</p>
                    @error('guardian_phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- FOTO --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Foto <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">Foto terbaru dengan latar belakang bersih atau polos. Foto ini akan digunakan untuk sertifikat. Ukuran maksimal 500 KB (JPG, PNG, WEBP).</p>
                    @if(isset($prevFoto) && $prevFoto)
                    <div class="flex items-center gap-4 mb-3 p-3 bg-green-50 border border-green-200 rounded-xl">
                        <img src="{{ asset('storage/' . $prevFoto) }}" alt="Foto sebelumnya"
                             class="w-16 h-20 object-cover rounded-lg border border-green-300 flex-shrink-0">
                        <div>
                            <p class="text-xs font-semibold text-green-700">Foto sudah diunggah sebelumnya.</p>
                            <p class="text-xs text-green-600 mt-1">Biarkan kosong untuk tetap menggunakan foto ini, atau pilih file baru untuk menggantinya.</p>
                        </div>
                    </div>
                    @endif
                    <div id="foto-status" data-ada="{{ (isset($prevFoto) && $prevFoto) ? 'true' : 'false' }}"></div>
                    <input type="file" id="photo_input" name="photo" accept="image/jpeg,image/jpg,image/png,image/webp"
                           class="w-full border @error('photo') border-red-400 @else border-sc-line @enderror rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sc-teal-600 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-sc-teal-600 file:text-white hover:file:bg-sc-teal-700 cursor-pointer">
                    @error('photo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CATATAN / NOTE --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Catatan <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        Tuliskan hari yang tidak memungkinkan untuk belajar beserta alasannya.
                        Contoh: <em>"Selasa tidak bisa karena ada kegiatan PPA, jadi hanya bisa Senin, Rabu, dan Kamis."</em>
                        Catatan ini akan menjadi bahan pertimbangan, namun tidak dijamin dapat dipenuhi.
                    </p>
                    <textarea name="note" rows="3"
                              placeholder="Tulis catatan jadwal Anda di sini (jika ada)..."
                              class="w-full border @error('note') border-red-400 @else border-sc-line @enderror rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20 resize-none">{{ old('note', $prevData['note'] ?? '') }}</textarea>
                    @error('note')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" id="submit-btn" disabled
                            class="w-full py-3 bg-sc-teal-600 text-white rounded-xl font-semibold hover:bg-sc-teal-700 transition text-base disabled:opacity-40 disabled:cursor-not-allowed">
                        Kirim Pendaftaran
                    </button>
                    <p id="submit-hint" class="text-center text-xs text-orange-500 mt-2 hidden">
                        Lengkapi semua field yang bertanda * sebelum mengirim.
                    </p>
                    <p class="text-center text-xs text-gray-400 mt-2">
                        Dengan mengirim form ini, data Anda akan disimpan dan menunggu validasi dari pengurus.
                    </p>
                </div>

            </form>
        </div>

    </div>
</div>
@push('scripts')
<script>
    // Flag: apakah foto dari session sudah ada
    window._fotoSudahAda = document.getElementById('foto-status')?.dataset?.ada === 'true';

    // --- Cek kelengkapan form → enable/disable tombol submit ---
    function checkForm() {
        const name     = document.getElementById('name')?.value.trim() || '';
        const gender   = document.querySelector('input[name="gender"]:checked');
        const school   = document.getElementById('school_name')?.value.trim() || '';
        const grade    = document.getElementById('grade_class')?.value.trim() || '';
        const birth    = document.getElementById('birth_date')?.value || '';
        const address  = document.getElementById('address')?.value.trim() || '';
        const guardian = document.getElementById('guardian_phone')?.value.trim() || '';
        const photo    = document.getElementById('photo_input');
        const hasPhoto = (photo && photo.files && photo.files.length > 0) || window._fotoSudahAda;

        const ok = name && gender && school && grade && birth && address && guardian.length >= 7 && hasPhoto;
        const btn  = document.getElementById('submit-btn');
        const hint = document.getElementById('submit-hint');
        btn.disabled = !ok;
        if (hint) hint.classList.toggle('hidden', ok);
    }

    // Pantau semua required field
    ['name','school_name','grade_class','birth_date','address','guardian_phone'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', checkForm);
    });
    document.querySelectorAll('input[name="gender"]').forEach(function(el) {
        el.addEventListener('change', checkForm);
    });
    const photoInput = document.getElementById('photo_input');
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            if (this.files.length > 0) window._fotoSudahAda = true;
            checkForm();
        });
    }

    // Run on page load (restore state setelah kembali dari preview)
    checkForm();

    // --- Nama sekolah: live uppercase ---
    document.getElementById('school_name').addEventListener('input', function () {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });

    // --- HP fields: hanya terima digit ---
    ['guardian_phone', 'student_phone'].forEach(function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('keydown', function (e) {
            const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
            if (allowed.includes(e.key)) return;
            if (e.key >= '0' && e.key <= '9') return;
            e.preventDefault();
        });
        el.addEventListener('input', function () {
            const clean = this.value.replace(/\D/g, '');
            if (this.value !== clean) this.value = clean;
        });
        el.addEventListener('paste', function (e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            const digits = text.replace(/\D/g, '').substring(0, 13);
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const current = this.value;
            this.value = (current.substring(0, start) + digits + current.substring(end)).substring(0, 13);
        });
    });
</script>
@endpush
@endsection
