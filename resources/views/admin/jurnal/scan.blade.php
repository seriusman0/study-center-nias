@extends('layouts.admin')
@section('page-title', 'Scan Foto Jurnal Offline')

@section('content')
<a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-link mb-3">
    <i class="fas fa-arrow-left"></i> Dashboard
</a>

<div class="row">
    {{-- Panel Upload --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header font-weight-bold">
                <i class="fas fa-camera mr-1 text-primary"></i> Upload Foto Jurnal
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Upload foto halaman jurnal harian yang sudah diisi siswa. Sistem akan membaca centangan secara otomatis.
                    Satu foto = satu halaman minggu.
                </p>

                <div id="drop-zone"
                     class="border-2 border-dashed rounded p-4 text-center mb-3"
                     style="border:2px dashed #adb5bd;cursor:pointer;transition:background .2s"
                     ondragover="event.preventDefault();this.style.background='#e8f4f8'"
                     ondragleave="this.style.background=''"
                     ondrop="handleDrop(event)">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="mb-1 small text-muted">Drag & drop foto di sini</p>
                    <p class="small text-muted">atau</p>
                    <label class="btn btn-sm btn-outline-primary mb-0">
                        Pilih Foto
                        <input type="file" id="file-input" accept="image/*" multiple class="d-none" onchange="handleFiles(this.files)">
                    </label>
                    <p class="small text-muted mt-2">JPEG / PNG · Maks. 10 MB per foto</p>
                </div>

                <div id="upload-queue"></div>
            </div>
        </div>

        <div class="card mt-3" id="confirm-panel" style="display:none!important">
            <div class="card-header font-weight-bold text-success">
                <i class="fas fa-check-circle mr-1"></i> Siap Disimpan
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Verifikasi nama siswa dan centangan di panel kanan, lalu klik tombol di bawah.
                </p>
                <button class="btn btn-success btn-block" onclick="saveAll()">
                    <i class="fas fa-save mr-1"></i> Simpan Semua ke Jurnal
                </button>
                <div id="save-result" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Panel Hasil --}}
    <div class="col-md-7">
        <div id="results-container">
            <div class="text-center text-muted py-5" id="empty-state">
                <i class="fas fa-image fa-3x mb-3 d-block text-light"></i>
                Hasil scan akan muncul di sini setelah foto diupload.
            </div>
        </div>
    </div>
</div>

{{-- Template kartu hasil --}}
<template id="result-card-template">
    <div class="card mb-3 result-card" data-scan-id="">
        <div class="card-header d-flex align-items-center">
            <span class="filename font-weight-bold text-truncate mr-2" style="max-width:200px"></span>
            <span class="badge badge-secondary status-badge ml-auto">Menunggu</span>
        </div>
        <div class="card-body scan-body">
            <div class="scanning-state text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary mr-2"></div>
                <span class="text-muted small">Claude sedang membaca foto...</span>
            </div>
            <div class="result-state" style="display:none">
                {{-- Nama siswa --}}
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Siswa <span class="text-danger">*</span></label>
                    <select class="form-control form-control-sm student-select">
                        <option value="">-- Pilih siswa --</option>
                    </select>
                    <small class="text-muted nama-ai"></small>
                </div>
                {{-- Tabel centang --}}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 check-table" style="font-size:11px">
                        <thead class="thead-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="failed-state" style="display:none">
                <div class="alert alert-danger py-2 small mb-0">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <span class="error-msg"></span>
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
const UPLOAD_URL  = '{{ route("admin.jurnal.scan.upload") }}';
const STATUS_URL  = '{{ route("admin.jurnal.scan.status", ["scan" => "__ID__"]) }}';
const CONFIRM_URL = '{{ route("admin.jurnal.scan.confirm") }}';
const CSRF        = '{{ csrf_token() }}';

const scanCards = {}; // scanId → {card, data}

// ── Upload ──────────────────────────────────────────────────────────
function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.style.background = '';
    handleFiles(e.dataTransfer.files);
}

function handleFiles(files) {
    [...files].forEach(uploadFile);
}

async function uploadFile(file) {
    document.getElementById('empty-state')?.remove();

    const card = createPendingCard(file.name);
    document.getElementById('results-container').prepend(card);

    const fd = new FormData();
    fd.append('photo', file);
    fd.append('_token', CSRF);

    try {
        const res  = await fetch(UPLOAD_URL, { method: 'POST', body: fd });
        const json = await res.json();
        if (!json.scan_id) throw new Error('Upload gagal');

        card.dataset.scanId = json.scan_id;
        scanCards[json.scan_id] = { card, data: null };
        pollStatus(json.scan_id);
    } catch (err) {
        setFailed(card, 'Upload gagal: ' + err.message);
    }
}

// ── Polling ──────────────────────────────────────────────────────────
function pollStatus(scanId) {
    const url = STATUS_URL.replace('__ID__', scanId);
    const interval = setInterval(async () => {
        try {
            const res  = await fetch(url);
            const json = await res.json();

            if (json.status === 'done') {
                clearInterval(interval);
                setDone(scanCards[scanId].card, json);
                scanCards[scanId].data = json;
                checkConfirmPanel();
            } else if (json.status === 'failed') {
                clearInterval(interval);
                setFailed(scanCards[scanId].card, json.error || 'Gagal diproses.');
                checkConfirmPanel();
            }
        } catch {}
    }, 2000);
}

// ── Card builders ────────────────────────────────────────────────────
function createPendingCard(filename) {
    const tpl  = document.getElementById('result-card-template').content.cloneNode(true);
    const card = tpl.querySelector('.result-card');
    card.querySelector('.filename').textContent = filename;
    document.getElementById('results-container').prepend(card);
    return card;
}

function setDone(card, json) {
    card.querySelector('.status-badge').className = 'badge badge-success status-badge ml-auto';
    card.querySelector('.status-badge').textContent = 'Selesai';
    card.querySelector('.scanning-state').style.display = 'none';

    const rs = card.querySelector('.result-state');
    rs.style.display = '';

    // Nama AI
    const namaAi = json.result?.nama_siswa || '';
    card.querySelector('.nama-ai').textContent = namaAi ? `Terdeteksi: "${namaAi}"` : 'Nama tidak terdeteksi';

    // Dropdown siswa
    const sel = card.querySelector('.student-select');
    const matches = json.student_matches || [];
    matches.forEach(m => {
        const opt = new Option(`${m.name} (@${m.username})${m.cabang ? ' — ' + m.cabang : ''}`, m.id);
        sel.add(opt);
    });
    if (matches.length === 1) sel.value = matches[0].id;

    // Tabel centang
    buildCheckTable(card, json.result?.hari || {});
}

function setFailed(card, msg) {
    card.querySelector('.status-badge').className = 'badge badge-danger status-badge ml-auto';
    card.querySelector('.status-badge').textContent = 'Gagal';
    card.querySelector('.scanning-state').style.display = 'none';
    card.querySelector('.failed-state').style.display = '';
    card.querySelector('.error-msg').textContent = msg;
}

function buildCheckTable(card, hari) {
    const dates = Object.keys(hari).sort();
    if (dates.length === 0) return;

    // Kumpulkan semua kolom item dari hari pertama
    const firstDay = hari[dates[0]] || {};
    const itemKeys = Object.keys(firstDay);

    const thead = card.querySelector('.check-table thead');
    const tbody = card.querySelector('.check-table tbody');

    // Header
    const hr = document.createElement('tr');
    hr.innerHTML = '<th>Tanggal</th>' + itemKeys.map(k => `<th class="text-center">${k.toUpperCase()}</th>`).join('');
    thead.appendChild(hr);

    // Baris data
    dates.forEach(d => {
        const row    = document.createElement('tr');
        const items  = hari[d] || {};
        const dayStr = formatDate(d);
        let cells    = `<td class="text-nowrap font-weight-bold">${dayStr}</td>`;
        itemKeys.forEach(k => {
            const checked = items[k] ? 'checked' : '';
            cells += `<td class="text-center">
                <input type="checkbox" ${checked} data-date="${d}" data-key="${k}" onchange="markEdited(this)">
            </td>`;
        });
        row.innerHTML = cells;
        tbody.appendChild(row);
    });
}

function formatDate(d) {
    const days = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    const dt   = new Date(d + 'T00:00:00');
    return days[dt.getDay()] + ', ' + dt.getDate() + '/' + (dt.getMonth()+1);
}

function markEdited(cb) {
    cb.closest('td').style.background = '#fff3cd';
}

// ── Confirm panel ────────────────────────────────────────────────────
function checkConfirmPanel() {
    const done = Object.values(scanCards).some(s => s.data?.status === 'done');
    document.getElementById('confirm-panel').style.display = done ? '' : 'none!important';
    if (done) document.getElementById('confirm-panel').style.cssText = '';
}

async function saveAll() {
    const entries = [];

    for (const [scanId, sc] of Object.entries(scanCards)) {
        if (!sc.data || sc.data.status !== 'done') continue;

        const card      = sc.card;
        const studentId = card.querySelector('.student-select').value;
        if (!studentId) {
            alert('Pilih siswa untuk semua foto yang berhasil di-scan.');
            return;
        }

        // Kumpulkan centang dari tabel
        const hari = {};
        card.querySelectorAll('.check-table input[type=checkbox]').forEach(cb => {
            const date = cb.dataset.date;
            const key  = cb.dataset.key;
            if (!hari[date]) hari[date] = {};
            hari[date][key] = cb.checked;
        });

        entries.push({ student_id: parseInt(studentId), hari });
    }

    if (entries.length === 0) { alert('Tidak ada data untuk disimpan.'); return; }

    const btn = document.querySelector('#confirm-panel .btn-success');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

    try {
        const res  = await fetch(CONFIRM_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body:    JSON.stringify({ entries }),
        });
        const json = await res.json();

        document.getElementById('save-result').innerHTML =
            `<div class="alert alert-success py-2 small"><i class="fas fa-check mr-1"></i>
             Berhasil disimpan: <strong>${json.saved_days}</strong> hari jurnal.</div>`;
    } catch (err) {
        document.getElementById('save-result').innerHTML =
            `<div class="alert alert-danger py-2 small">Gagal: ${err.message}</div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan Semua ke Jurnal';
    }
}
</script>
@endpush
@endsection
