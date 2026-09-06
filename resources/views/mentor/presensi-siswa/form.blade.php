@extends('layouts.app')
@section('title', ($presensi ? 'Edit' : 'Catat') . ' Presensi Siswa - Study Center Nias')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap4.min.css">
<style>
    .ts-wrapper.multi .ts-control > div {
        background:#1d4ed8!important;color:#fff!important;border-radius:6px;padding:2px 8px;margin:2px;font-size:12px;
    }
    .ts-dropdown .item-row { display:flex;flex-direction:column;padding:6px 8px; }
    .ts-dropdown .item-row .nm { font-weight:600;color:#1f2937; }
    .ts-dropdown .item-row .meta { font-size:11px;color:#6b7280; }
    .ts-dropdown .item-row .meta .badge { background:#eff6ff;color:#1e40af;padding:1px 6px;border-radius:4px;margin-right:4px;font-size:10px; }
    .student-row {
        display:grid;grid-template-columns:1fr 110px 32px;gap:8px;align-items:center;
        padding:10px 0;border-bottom:1px solid #f1f5f9;
    }
    .student-row .nm { font-weight:600;font-size:13px;color:#111827; }
    .student-row .meta { font-size:11px;color:#6b7280; }
    .student-row select { font-size:12px;padding:4px 8px;border:1px solid #d1d5db;border-radius:8px;background:#fff; }
    #ts-kelasPicker { border:1px solid #d1d5db;border-radius:10px;padding:8px 12px;font-size:14px; }
    .ts-wrapper.single .ts-control { padding:8px 12px;border-radius:10px;border:1px solid #d1d5db; }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('presensi.index') }}"
           class="w-9 h-9 rounded-xl bg-white border border-sc-line flex items-center justify-center flex-shrink-0 hover:bg-gray-50 transition">
            <svg width="16" height="16" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <h1 class="text-lg font-bold text-sc-ink-900">{{ $presensi ? 'Edit Presensi' : 'Catat Presensi Siswa' }}</h1>
    </div>

    <form method="POST"
          action="{{ $presensi ? route('presensi.update', $presensi->id) : route('presensi.store') }}"
          enctype="multipart/form-data" id="presensiForm" class="space-y-4">
        @csrf
        @if($presensi) @method('PUT') @endif

        {{-- Detail Sesi --}}
        <div class="bg-white rounded-2xl border border-sc-line shadow-sc-2 p-5 space-y-4">
            <h2 class="text-sm font-bold text-sc-ink-700 uppercase tracking-wide">Detail Sesi</h2>

            {{-- Mentor (hidden = self) --}}
            <input type="hidden" name="mentor_id" value="{{ $defaultMentorId }}">

            {{-- Cabang (hidden = mentor's cabang) --}}
            <input type="hidden" name="cabang_id" value="{{ auth()->user()->cabang_id }}">

            {{-- Kelas --}}
            <div>
                <label class="block text-sm font-semibold text-sc-ink-700 mb-1.5">Nama Kelas <span class="text-red-500">*</span></label>
                <select id="kelasPicker" name="kelas_id" required></select>
                <p class="text-xs text-sc-ink-400 mt-1">
                    Belum ada kelas?
                    <a href="{{ route('mentor.kelas-master.index') }}" class="text-sc-teal-600 hover:underline">Tambah di Kelas Master</a>
                </p>
            </div>

            {{-- Tanggal & Jam --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-3 sm:col-span-1">
                    <label class="block text-sm font-semibold text-sc-ink-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" required
                           value="{{ old('tanggal', $presensi?->tanggal?->format('Y-m-d') ?? date('Y-m-d')) }}"
                           class="w-full rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-sc-ink-700 mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_mulai" required
                           value="{{ old('jam_mulai', $presensi ? substr($presensi->jam_mulai,0,5) : '') }}"
                           class="w-full rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-sc-ink-700 mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_selesai" required
                           value="{{ old('jam_selesai', $presensi ? substr($presensi->jam_selesai,0,5) : '') }}"
                           class="w-full rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500">
                </div>
            </div>

            {{-- Foto --}}
            <div>
                <label class="block text-sm font-semibold text-sc-ink-700 mb-1.5">Foto Kegiatan <span class="text-xs font-normal text-sc-ink-400">(opsional, maks 4MB)</span></label>
                <input type="file" name="foto" accept="image/*"
                       class="w-full text-sm text-sc-ink-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-sc-teal-50 file:text-sc-teal-700 file:font-medium hover:file:bg-sc-teal-100">
                @if($presensi?->foto)
                <img src="{{ asset('storage/' . $presensi->foto) }}" class="mt-2 rounded-xl max-h-20 object-cover" alt="Foto">
                @endif
            </div>

            {{-- Materi --}}
            <div>
                <label class="block text-sm font-semibold text-sc-ink-700 mb-1.5">Materi yang Diajarkan <span class="text-red-500">*</span></label>
                <textarea name="materi" rows="3" required maxlength="5000"
                          placeholder="Topik / sub-topik yang dibahas..."
                          class="w-full rounded-xl border border-sc-line px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-500 resize-none">{{ old('materi', $presensi?->materi) }}</textarea>
            </div>
        </div>

        {{-- Daftar Siswa --}}
        <div class="bg-white rounded-2xl border border-sc-line shadow-sc-2 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-sc-ink-700 uppercase tracking-wide">Daftar Siswa</h2>
                @if($presensi)
                <button type="button" id="btnAktifkanKamera"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-sc-teal-700 border border-sc-teal-300 rounded-lg hover:bg-sc-teal-50 transition">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    Scan QR
                </button>
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold text-sc-ink-700 mb-1.5">Pilih Siswa <span class="text-red-500">*</span></label>
                <select id="studentPicker" multiple placeholder="Ketik untuk mencari siswa..."></select>
                <p class="text-xs text-sc-ink-400 mt-1">Bisa pilih banyak siswa sekaligus.</p>
            </div>

            <div id="selectedStudents" class="space-y-0"></div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#007a5c;color:#fff;font-size:.875rem;font-weight:600;border-radius:10px;border:none;cursor:pointer;line-height:1.25rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ $presensi ? 'Simpan Perubahan' : 'Simpan Presensi' }}
            </button>
            <a href="{{ route('presensi.index') }}"
               style="display:inline-flex;align-items:center;padding:10px 20px;font-size:.875rem;font-weight:500;color:#374151;border:1.5px solid #d1d5db;border-radius:10px;text-decoration:none;line-height:1.25rem;background:#fff;">
                Batal
            </a>
        </div>
    </form>

</div>

{{-- QR Scanner Modal --}}
@if($presensi)
<div id="scannerModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
        <div class="px-5 py-4 border-b border-sc-line flex items-center justify-between">
            <h2 class="text-base font-bold text-sc-ink-900">Scan QR Siswa</h2>
            <button type="button" id="btnTutupScanner"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400 text-xl">✕</button>
        </div>
        <div class="p-4">
            <div id="qr-reader" style="width:100%"></div>
            <div id="scan-result" class="mt-3 hidden">
                <div id="scan-result-inner" class="p-3 rounded-xl text-sm"></div>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-sc-line flex justify-end">
            <button type="button" id="btnTutupScanner2"
                    class="px-4 py-2 text-sm border border-sc-line text-sc-ink-600 rounded-xl hover:bg-gray-50">Tutup</button>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
@if($presensi)
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function() {
    const scanUrl = '/presensi/{{ $presensi->id }}/scan';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let scanner = null, scanning = false;

    function escapeHtml(s) {
        if (!s) return '';
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    let audioCtx = null;
    try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); } catch(e) {}
    function beep(freq, dur) {
        if (!audioCtx) return;
        try {
            const osc = audioCtx.createOscillator(), g = audioCtx.createGain();
            osc.connect(g); g.connect(audioCtx.destination);
            osc.frequency.value = freq; g.gain.setValueAtTime(0.3, audioCtx.currentTime);
            g.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + dur);
            osc.start(); osc.stop(audioCtx.currentTime + dur);
        } catch(e) {}
    }

    function initScanner() {
        scanner = new Html5Qrcode('qr-reader');
        scanner.start({facingMode:'environment'},{fps:10,qrbox:{width:250,height:250}},onScanSuccess,()=>{})
            .catch(() => showResult('error','Kamera tidak dapat diakses.'));
    }
    function stopScanner() {
        if (scanner) { scanner.stop().catch(()=>{}).finally(()=>{ scanner=null; scanning=false; }); }
    }
    function onScanSuccess(text) {
        if (scanning) return; scanning = true;
        const id = parseInt(text.trim(), 10);
        if (isNaN(id)) { showResult('error','QR tidak valid.'); beep(200,0.4); setTimeout(()=>scanning=false,2000); return; }
        fetch(scanUrl, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:JSON.stringify({student_id:id})})
            .then(r=>r.json()).then(data=>{
                if (data.status==='added') {
                    beep(880,0.15);
                    showResult('success','<strong>'+escapeHtml(data.student.name)+'</strong> ditambahkan.');
                    window.__presensiAddStudent && window.__presensiAddStudent(data.student);
                } else if (data.status==='already') {
                    beep(600,0.15);
                    showResult('warn','<strong>'+escapeHtml(data.student.name)+'</strong> sudah presensi.');
                } else {
                    beep(200,0.4); showResult('error','ID siswa tidak ditemukan.');
                }
                setTimeout(()=>scanning=false,2500);
            }).catch(()=>{ beep(200,0.4); showResult('error','Koneksi gagal.'); setTimeout(()=>scanning=false,2000); });
    }
    function showResult(type, html) {
        const wrap = document.getElementById('scan-result');
        const inner = document.getElementById('scan-result-inner');
        const cls = {success:'bg-green-50 text-green-800 border border-green-200',warn:'bg-yellow-50 text-yellow-800 border border-yellow-200',error:'bg-red-50 text-red-800 border border-red-200'};
        inner.className = 'p-3 rounded-xl text-sm ' + (cls[type]||'');
        inner.innerHTML = html;
        wrap.classList.remove('hidden');
    }

    document.getElementById('btnAktifkanKamera')?.addEventListener('click', () => {
        if (audioCtx?.state==='suspended') audioCtx.resume();
        document.getElementById('scannerModal').classList.remove('hidden');
        initScanner();
    });
    ['btnTutupScanner','btnTutupScanner2'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', () => {
            document.getElementById('scannerModal').classList.add('hidden');
            stopScanner();
        });
    });
})();
</script>
@endif
<script>
(function() {
    const allKelas = @json($kelasList);
    @php $initKelasId = $presensi?->kelas_id ? (string) $presensi->kelas_id : null; @endphp
    const initialKelasId = @json($initKelasId);

    const tsKelas = new TomSelect('#kelasPicker', {
        valueField:'id', labelField:'label', searchField:['nama','label'],
        options: allKelas, maxOptions:50, placeholder:'Pilih atau cari kelas...',
        render: { option: (item, escape) => `<div><strong>${escape(item.nama)}</strong>${item.cabang?` <small style="color:#6b7280">— ${escape(item.cabang)}</small>`:''}</div>` },
    });
    if (initialKelasId) tsKelas.addItem(initialKelasId, true);

    const searchUrl = @json(route('presensi.students.search'));
    const selectedContainer = document.getElementById('selectedStudents');
    const initial = @json($selectedStudents);

    function escapeHtml(s) {
        if (!s) return '';
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function ensureRow(student) {
        if (document.querySelector('.student-row[data-id="'+student.id+'"]')) return;
        const row = document.createElement('div');
        row.className = 'student-row';
        row.dataset.id = student.id;
        const meta = [student.kelas, student.cabang].filter(Boolean).join(' · ') || '—';
        row.innerHTML = `
            <div>
                <div class="nm">${escapeHtml(student.name)}</div>
                <div class="meta">${escapeHtml(meta)}</div>
                <input type="hidden" name="student_ids[]" value="${student.id}">
            </div>
            <select name="student_status[${student.id}]">
                <option value="hadir" ${!student.status||student.status==='hadir'?'selected':''}>Hadir</option>
                <option value="izin"  ${student.status==='izin' ?'selected':''}>Izin</option>
                <option value="sakit" ${student.status==='sakit'?'selected':''}>Sakit</option>
                <option value="alpha" ${student.status==='alpha'?'selected':''}>Alpha</option>
            </select>
            <button type="button" onclick="this.closest('.student-row').remove();ts.removeItem('${student.id}',true)"
                style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:18px;line-height:1;">×</button>`;
        selectedContainer.appendChild(row);
    }

    const ts = new TomSelect('#studentPicker', {
        valueField:'id', labelField:'name', searchField:['name','kelas','school','cabang'],
        maxOptions:50, plugins:['remove_button'],
        load: (q, cb) => fetch(searchUrl+'?q='+encodeURIComponent(q)).then(r=>r.json()).then(j=>cb(j.data||[])).catch(()=>cb([])),
        render: {
            option: (item, escape) => {
                const meta = [item.kelas,item.school,item.cabang].filter(Boolean).map(v=>`<span class="badge">${escape(v)}</span>`).join('');
                return `<div class="item-row"><div class="nm">${escape(item.name)}</div><div class="meta">${meta||'<span style="color:#9ca3af">tanpa info</span>'}</div></div>`;
            },
            item: (item, escape) => `<div>${escape(item.name)}${item.kelas?' · '+escape(item.kelas):''}${item.cabang?' · '+escape(item.cabang):''}</div>`,
        },
        onItemAdd: function(val) { const opt = this.options[val]; if (opt) ensureRow(opt); },
        onItemRemove: val => { const r = document.querySelector('.student-row[data-id="'+val+'"]'); if(r) r.remove(); },
    });

    initial.forEach(s => { ts.addOption(s); ts.addItem(String(s.id),true); ensureRow(s); });

    window.__presensiAddStudent = function(student) {
        if (!ts.options[String(student.id)]) ts.addOption(student);
        ts.addItem(String(student.id),true);
        ensureRow(student);
    };
})();
</script>
@endpush
@endsection
