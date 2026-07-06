@extends('layouts.app')

@section('title', 'Edit Jurnal')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6"
     x-data="{
        step: 1,
        totalSteps: 4,
        next() { if (this.step < this.totalSteps) this.step++ },
        prev() { if (this.step > 1) this.step-- },
     }">

    <div class="mb-5 flex items-center gap-3">
        <a href="{{ route('scholarship-journal.show', $journal->id) }}" class="text-sc-ink-400 hover:text-sc-ink-700 text-sm">&larr; Kembali</a>
        <h1 class="font-display text-xl text-sc-ink-900">Edit Jurnal</h1>
    </div>

    @if($journal->status === 'revision_required' && $journal->reviewer_notes)
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="text-xs font-bold text-red-600 mb-1">Catatan Revisi</div>
        <p class="text-sm text-red-800">{{ $journal->reviewer_notes }}</p>
    </div>
    @endif

    {{-- Step indicator --}}
    <div class="flex items-center gap-2 mb-6">
        @foreach(['Akademik', 'Aktivitas', 'Pelayanan', 'Refleksi'] as $i => $label)
        <div class="flex items-center gap-2 {{ $i < 3 ? 'flex-1' : '' }}">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0 transition"
                 :class="{{ $i + 1 }} <= step ? 'bg-sc-teal-600 text-white' : 'bg-sc-ink-100 text-sc-ink-400'">
                {{ $i + 1 }}
            </div>
            <span class="text-xs hidden sm:block"
                  :class="{{ $i + 1 }} === step ? 'text-sc-teal-700 font-semibold' : 'text-sc-ink-400'">
                {{ $label }}
            </span>
            @if($i < 3)
            <div class="flex-1 h-0.5 bg-sc-line mx-1"></div>
            @endif
        </div>
        @endforeach
    </div>

    @php $item = $journal->item; @endphp

    <form method="POST" action="{{ route('scholarship-journal.update', $journal->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Step 1 --}}
        <div x-show="step === 1">
            <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 space-y-4">
                <h2 class="font-bold text-sc-ink-900">Periode & Data Akademik</h2>
                <div class="p-3 bg-sc-ink-50 rounded-xl text-sm text-sc-ink-600">
                    Periode: <strong>{{ $journal->title }}</strong> (tidak dapat diubah)
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-sc-ink-600 mb-1">IPS Semester Ini</label>
                        <input type="number" name="gpa_current_semester" step="0.01" min="0" max="4"
                               class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500"
                               value="{{ old('gpa_current_semester', $item?->gpa_current_semester) }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-sc-ink-600 mb-1">IPK Kumulatif</label>
                        <input type="number" name="cumulative_gpa" step="0.01" min="0" max="4"
                               class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500"
                               value="{{ old('cumulative_gpa', $item?->cumulative_gpa) }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Persentase Kehadiran Kuliah (%)</label>
                    <input type="number" name="class_attendance_percentage" min="0" max="100"
                           class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500"
                           value="{{ old('class_attendance_percentage', $item?->class_attendance_percentage) }}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Ringkasan Perkuliahan</label>
                    <textarea name="academic_summary" rows="3"
                              class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500 resize-none">{{ old('academic_summary', $item?->academic_summary) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Step 2 --}}
        <div x-show="step === 2" style="display:none">
            <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 space-y-4">
                <h2 class="font-bold text-sc-ink-900">Aktivitas & Pengembangan Diri</h2>
                <div>
                    <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Kegiatan Organisasi / UKM</label>
                    <textarea name="organization_activities" rows="3"
                              class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500 resize-none">{{ old('organization_activities', $item?->organization_activities) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Pelatihan / Seminar</label>
                    <textarea name="training_seminars" rows="3"
                              class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500 resize-none">{{ old('training_seminars', $item?->training_seminars) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Prestasi</label>
                    <textarea name="achievements" rows="2"
                              class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500 resize-none">{{ old('achievements', $item?->achievements) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Step 3 --}}
        <div x-show="step === 3" style="display:none">
            <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 space-y-4">
                <h2 class="font-bold text-sc-ink-900">Pelayanan & Pengabdian</h2>
                <div>
                    <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Bentuk Pelayanan</label>
                    <textarea name="community_service_details" rows="4"
                              class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500 resize-none">{{ old('community_service_details', $item?->community_service_details) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Estimasi Jam Pelayanan</label>
                    <input type="number" name="service_hours" min="0"
                           class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500"
                           value="{{ old('service_hours', $item?->service_hours) }}">
                </div>
            </div>
        </div>

        {{-- Step 4 --}}
        <div x-show="step === 4" style="display:none">
            <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 space-y-4">
                <h2 class="font-bold text-sc-ink-900">Refleksi & Lampiran Tambahan</h2>
                <div>
                    <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Refleksi Rohani</label>
                    <textarea name="personal_reflection" rows="4"
                              class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500 resize-none">{{ old('personal_reflection', $item?->personal_reflection) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Target Bulan Depan</label>
                    <textarea name="next_month_goals" rows="3"
                              class="w-full border border-sc-line rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-500 resize-none">{{ old('next_month_goals', $item?->next_month_goals) }}</textarea>
                </div>

                @if($journal->attachments->count())
                <div>
                    <div class="text-xs font-semibold text-sc-ink-500 mb-2">Lampiran Tersimpan</div>
                    @foreach($journal->attachments as $att)
                    <div class="text-xs text-sc-ink-600 py-1 border-b border-sc-line flex justify-between">
                        <span>{{ $att->file_name }}</span>
                        <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="text-sc-teal-600">Buka</a>
                    </div>
                    @endforeach
                </div>
                @endif

                <div x-data="{ files: [] }">
                    <label class="block text-xs font-semibold text-sc-ink-600 mb-1">Tambah Lampiran</label>
                    <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.docx"
                           class="w-full text-sm text-sc-ink-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-sc-teal-50 file:text-sc-teal-700 hover:file:bg-sc-teal-100"
                           @change="files = Array.from($event.target.files)">
                    <template x-for="(file, idx) in files" :key="idx">
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs text-sc-ink-600 flex-1 truncate" x-text="file.name"></span>
                            <select :name="'attachment_types[' + idx + ']'"
                                    class="text-xs border border-sc-line rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-sc-teal-500">
                                <option value="transkrip_khs">Transkrip / KHS</option>
                                <option value="sertifikat">Sertifikat</option>
                                <option value="foto_kegiatan">Foto Kegiatan</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="mt-4 flex items-center justify-between">
            <button type="button" @click="prev()" x-show="step > 1"
                    class="px-4 py-2 border border-sc-line rounded-xl text-sm font-semibold text-sc-ink-600 hover:bg-sc-ink-50">
                &larr; Sebelumnya
            </button>
            <div x-show="step <= 1"></div>
            <div class="flex gap-2" x-show="step === totalSteps">
                <button type="submit" name="action" value="draft"
                        class="px-4 py-2 border border-sc-line rounded-xl text-sm font-semibold text-sc-ink-600 hover:bg-sc-ink-50">
                    Simpan Draft
                </button>
                <button type="submit" name="action" value="submit"
                        class="px-5 py-2 bg-sc-teal-600 text-white rounded-xl text-sm font-bold hover:bg-sc-teal-700">
                    Kirim Jurnal
                </button>
            </div>
            <button type="button" @click="next()" x-show="step < totalSteps"
                    class="px-5 py-2 bg-sc-teal-600 text-white rounded-xl text-sm font-bold hover:bg-sc-teal-700">
                Lanjut &rarr;
            </button>
        </div>
    </form>
</div>
@endsection
