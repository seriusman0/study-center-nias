@extends('layouts.admin')

@section('page-title', $presensi ? 'Edit Presensi' : 'Catat Presensi')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap4.min.css">
<style>
    .ts-wrapper.multi .ts-control > div {
        background: #1e3a5f !important; color: #fff !important;
        border-radius: 4px; padding: 2px 8px; margin: 2px;
    }
    .ts-dropdown .item-row {
        display: flex; flex-direction: column; padding: 4px 2px;
    }
    .ts-dropdown .item-row .nm { font-weight: 600; color: #1f2937; }
    .ts-dropdown .item-row .meta { font-size: 11px; color: #6b7280; }
    .ts-dropdown .item-row .meta .badge {
        background: #eef2f7; color: #1e3a5f; padding: 1px 6px; border-radius: 4px; margin-right: 4px;
    }
    .student-row {
        border-bottom: 1px solid #f1f5f9; padding: 6px 0;
        display: grid; grid-template-columns: 1fr 100px 28px; gap: 8px; align-items: center;
    }
    .student-row .info .nm { font-weight: 600; }
    .student-row .info .meta { font-size: 11px; color: #6b7280; }
    .student-row select { font-size: 12px; padding: 2px 6px; height: 30px; }
</style>
@endpush

@section('content')
<form method="POST"
      action="{{ $presensi ? route('presensi.update', $presensi->id) : route('presensi.store') }}"
      enctype="multipart/form-data" id="presensiForm">
    @csrf
    @if($presensi) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Detail Sesi</h6></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Mentor / Guru <span class="text-danger">*</span></label>
                    <select name="mentor_id" class="form-control" required>
                        <option value="">Pilih mentor...</option>
                        @foreach($mentors as $m)
                        <option value="{{ $m->id }}"
                            {{ old('mentor_id', $presensi?->mentor_id ?? $defaultMentorId) == $m->id ? 'selected' : '' }}>
                            {{ $m->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Cabang</label>
                    <select name="cabang_id" class="form-control">
                        <option value="">- Tidak ada -</option>
                        @foreach($cabangs as $c)
                        <option value="{{ $c->id }}" {{ old('cabang_id', $presensi?->cabang_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-5">
                    <label>Nama Kelas <span class="text-danger">*</span></label>
                    <select id="kelasPicker" name="kelas_id" required></select>
                    <small class="text-muted">Pilih kelas dari master. Jika belum ada, tambahkan di <a href="{{ route('admin.kelas-master.index') }}">Master Kelas</a>.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" class="form-control"
                           value="{{ old('tanggal', $presensi?->tanggal?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Jam Mulai <span class="text-danger">*</span></label>
                    <input type="time" name="jam_mulai" class="form-control"
                           value="{{ old('jam_mulai', $presensi ? substr($presensi->jam_mulai, 0, 5) : '') }}" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Jam Selesai <span class="text-danger">*</span></label>
                    <input type="time" name="jam_selesai" class="form-control"
                           value="{{ old('jam_selesai', $presensi ? substr($presensi->jam_selesai, 0, 5) : '') }}" required>
                </div>
                <div class="form-group col-md-5">
                    <label>Foto Kegiatan <span class="text-muted small">(opsional, maks 4MB)</span></label>
                    <input type="file" name="foto" accept="image/*" class="form-control-file">
                    @if($presensi?->foto)
                    <img src="{{ asset('storage/' . $presensi->foto) }}" class="mt-2 rounded" style="max-height:80px">
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label>Materi yang Diajarkan <span class="text-danger">*</span></label>
                <textarea name="materi" class="form-control" rows="3" required maxlength="5000"
                          placeholder="Topik / sub-topik yang dibahas">{{ old('materi', $presensi?->materi) }}</textarea>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Daftar Siswa Hadir</h6>
            <span class="text-muted small">Cari nama / sekolah / kelas. Format: <strong>Nama · Kelas · Cabang</strong></span>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Pilih Siswa <span class="text-danger">*</span></label>
                <select id="studentPicker" multiple placeholder="Ketik untuk mencari siswa..."></select>
                <small class="text-muted">Bisa pilih banyak siswa sekaligus.</small>
            </div>

            <div id="selectedStudents"></div>
        </div>
    </div>

    <div class="text-right mt-4 mb-5">
        <a href="{{ route('presensi.index') }}" class="btn btn-secondary">Batal</a>
        @if($presensi)
        <button type="button" class="btn btn-success" id="btnAktifkanKamera">
            <i class="fas fa-qrcode mr-1"></i> Aktifkan Kamera
        </button>
        @endif
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> {{ $presensi ? 'Simpan Perubahan' : 'Simpan Presensi' }}
        </button>
    </div>
</form>

@if($presensi)
<div class="modal fade" id="scannerModal" tabindex="-1" role="dialog" aria-labelledby="scannerModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scannerModalLabel"><i class="fas fa-qrcode mr-1"></i> Scan QR Siswa</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="qr-reader" style="width:100%"></div>
                <div id="scan-result" class="mt-3" style="display:none">
                    <div id="scan-result-inner" class="alert mb-0"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btnInputManual" data-dismiss="modal">
                    <i class="fas fa-keyboard mr-1"></i> Input Manual
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
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

    let scanner = null;
    let scanning = false;

    function escapeHtml(s) {
        if (!s) return '';
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    // --- Web Audio beeps (guarded — some mobile browsers restrict AudioContext) ---
    let audioCtx = null;
    try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); } catch(e) {}

    function beep(freq, duration, type, gain) {
        if (!audioCtx) return;
        try {
            type = type || 'sine'; gain = gain || 0.3;
            const osc = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            osc.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            osc.frequency.value = freq;
            osc.type = type;
            gainNode.gain.setValueAtTime(gain, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + duration);
        } catch(e) {}
    }

    function playSuccess()  { beep(880, 0.15); }
    function playWarning()  { beep(600, 0.12); setTimeout(() => beep(600, 0.12), 180); }
    function playError()    { beep(200, 0.4, 'sawtooth'); }

    // --- Scanner init/stop ---
    function initScanner() {
        scanner = new Html5Qrcode('qr-reader');
        scanning = false;
        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            () => {}
        ).catch(err => {
            showResult('danger', '<strong>Kamera tidak dapat diakses.</strong> Pastikan izin kamera sudah diberikan.');
        });
    }

    function stopScanner() {
        if (scanner) {
            scanner.stop().catch(() => {}).finally(() => { scanner = null; scanning = false; });
        }
    }

    // --- Handle scan result ---
    function onScanSuccess(decodedText) {
        if (scanning) return;
        scanning = true;

        const studentId = parseInt(decodedText.trim(), 10);
        if (isNaN(studentId)) {
            showResult('danger', '<strong>QR tidak valid.</strong> Format tidak dikenali.');
            playError();
            setTimeout(() => { scanning = false; }, 2000);
            return;
        }

        fetch(scanUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ student_id: studentId }),
        })
        .then(function(r) {
            if (!r.ok) {
                return r.text().then(function(t) {
                    throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 120));
                });
            }
            return r.json();
        })
        .then(function(data) {
            try {
                if (data.status === 'added') {
                    playSuccess();
                    var s = data.student;
                    showResult('success',
                        '<strong><i class="fas fa-check-circle mr-1"></i>' + escapeHtml(s.name) + '</strong> berhasil ditambahkan.' +
                        (s.kelas ? '<br><small class="text-muted">' + escapeHtml(s.kelas) + (s.cabang ? ' · ' + escapeHtml(s.cabang) : '') + '</small>' : '')
                    );
                    addStudentToList(s);
                    autoSave();
                } else if (data.status === 'already') {
                    playWarning();
                    var s2 = data.student;
                    showResult('warning',
                        '<i class="fas fa-exclamation-triangle mr-1"></i><strong>' + escapeHtml(s2.name) + '</strong> sudah melakukan presensi.'
                    );
                } else {
                    playError();
                    showResult('danger', '<i class="fas fa-times-circle mr-1"></i> ID siswa tidak ditemukan.');
                }
            } catch(displayErr) {
                showResult('warning', 'Scan OK tapi display error: ' + displayErr.message);
            }
            setTimeout(function() { scanning = false; }, 2500);
        })
        .catch(function(err) {
            playError();
            showResult('danger', 'Error: ' + (err && err.message ? err.message : 'koneksi gagal'));
            setTimeout(function() { scanning = false; }, 2000);
        });
    }

    function showResult(type, html) {
        const wrap = document.getElementById('scan-result');
        const inner = document.getElementById('scan-result-inner');
        inner.className = `alert alert-${type} mb-0`;
        inner.innerHTML = html;
        wrap.style.display = 'block';
    }

    function addStudentToList(student) {
        if (typeof window.__presensiAddStudent === 'function') {
            window.__presensiAddStudent(student);
        }
    }

    function autoSave() {
        var form = document.getElementById('presensiForm');
        if (!form) return;
        var formData = new FormData(form);
        fetch('/presensi/{{ $presensi->id }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData,
        }).catch(function() {});
    }

    // --- Modal lifecycle ---
    document.getElementById('btnAktifkanKamera')?.addEventListener('click', function() {
        if (audioCtx.state === 'suspended') audioCtx.resume();
        $('#scannerModal').modal('show');
    });

    $('#scannerModal').on('shown.bs.modal', function() {
        document.getElementById('scan-result').style.display = 'none';
        initScanner();
    });

    $('#scannerModal').on('hidden.bs.modal', function() {
        stopScanner();
    });

    document.getElementById('btnInputManual')?.addEventListener('click', function() {
        setTimeout(() => {
            const input = document.querySelector('#studentPicker-ts-control input');
            if (input) input.focus();
        }, 400);
    });
})();
</script>
@endif
<script>
(function() {
    // === Kelas master picker ===
    const allKelas = @json($kelasList);
    const cabangSelect = document.querySelector('select[name="cabang_id"]');
    @php
        $initKelasId = $presensi?->kelas_id ? (string) $presensi->kelas_id : null;
    @endphp
    const initialKelasId = @json($initKelasId);

    const tsKelas = new TomSelect('#kelasPicker', {
        valueField: 'id',
        labelField: 'label',
        searchField: ['nama', 'cabang', 'label'],
        options: allKelas,
        maxOptions: 50,
        placeholder: 'Pilih atau cari kelas...',
        render: {
            option: function(item, escape) {
                return `<div><strong>${escape(item.nama)}</strong>` +
                    (item.cabang ? ` <small class="text-muted">— ${escape(item.cabang)}</small>` : '') +
                    `</div>`;
            },
        },
    });
    if (initialKelasId) {
        tsKelas.addItem(initialKelasId, true);
    }
    cabangSelect?.addEventListener('change', function() {
        const cid = this.value;
        tsKelas.clear();
        tsKelas.clearOptions();
        const filtered = cid ? allKelas.filter(k => String(k.cabang_id) === cid) : allKelas;
        filtered.forEach(k => tsKelas.addOption(k));
        tsKelas.refreshOptions(false);
    });

    // === Student picker (existing) ===
    const searchUrl = @json(route('presensi.students.search'));
    const selectedContainer = document.getElementById('selectedStudents');
    const initial = @json($selectedStudents);

    const cache = new Map(); // id -> {name, kelas, cabang}

    function ensureRow(student) {
        cache.set(String(student.id), student);
        if (document.querySelector('.student-row[data-id="' + student.id + '"]')) return;
        const row = document.createElement('div');
        row.className = 'student-row';
        row.dataset.id = student.id;
        const meta = [student.kelas, student.cabang].filter(Boolean).join(' · ') || 'tanpa kelas/cabang';
        row.innerHTML = `
            <div class="info">
                <div class="nm">${escapeHtml(student.name)}</div>
                <div class="meta">${escapeHtml(meta)}</div>
                <input type="hidden" name="student_ids[]" value="${student.id}">
            </div>
            <select name="student_status[${student.id}]" class="form-control form-control-sm">
                <option value="hadir" ${student.status === 'hadir' || !student.status ? 'selected' : ''}>Hadir</option>
                <option value="izin"  ${student.status === 'izin'  ? 'selected' : ''}>Izin</option>
                <option value="sakit" ${student.status === 'sakit' ? 'selected' : ''}>Sakit</option>
                <option value="alpha" ${student.status === 'alpha' ? 'selected' : ''}>Alpha</option>
            </select>
            <button type="button" class="btn btn-xs btn-link text-danger remove-row" title="Hapus">
                <i class="fas fa-times"></i>
            </button>`;
        row.querySelector('.remove-row').addEventListener('click', function() {
            row.remove();
            ts.removeItem(String(student.id), true);
        });
        selectedContainer.appendChild(row);
    }

    function removeRow(id) {
        const row = document.querySelector('.student-row[data-id="' + id + '"]');
        if (row) row.remove();
    }

    function escapeHtml(s) {
        if (!s) return '';
        return String(s).replace(/[&<>"']/g, c => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[c]));
    }

    const ts = new TomSelect('#studentPicker', {
        valueField: 'id',
        labelField: 'name',
        searchField: ['name', 'kelas', 'school', 'cabang'],
        maxOptions: 50,
        plugins: ['remove_button'],
        load: function(query, callback) {
            fetch(searchUrl + '?q=' + encodeURIComponent(query))
                .then(r => r.json())
                .then(json => callback(json.data || []))
                .catch(() => callback([]));
        },
        render: {
            option: function(item, escape) {
                const meta = [item.kelas, item.school, item.cabang].filter(Boolean).map(v => `<span class="badge">${escape(v)}</span>`).join('');
                return `<div class="item-row">
                    <div class="nm">${escape(item.name)}</div>
                    <div class="meta">${meta || '<span class="text-muted">tanpa info</span>'}</div>
                </div>`;
            },
            item: function(item, escape) {
                const tag = item.kelas ? ' · ' + escape(item.kelas) : '';
                const cab = item.cabang ? ' · ' + escape(item.cabang) : '';
                return `<div>${escape(item.name)}${tag}${cab}</div>`;
            },
        },
        onItemAdd: function(value) {
            const opt = this.options[value];
            if (opt) ensureRow(opt);
        },
        onItemRemove: function(value) {
            removeRow(value);
        },
    });

    // Pre-load existing
    initial.forEach(s => {
        ts.addOption(s);
        ts.addItem(String(s.id), true);
        ensureRow(s);
    });

    // Exposed for QR scanner: add a scanned student without going through TomSelect search
    window.__presensiAddStudent = function(student) {
        if (!ts.options[String(student.id)]) {
            ts.addOption(student);
        }
        ts.addItem(String(student.id), true);
        ensureRow(student);
    };

})();
</script>
@endpush
@endsection
