@extends('layouts.admin')
@section('page-title', 'Jurnal Prajurit')

@section('content')


{{-- SCAN QR PRAJURIT BUTTON --}}
<div class="mb-4 text-center">
    <button type="button" class="btn btn-warning btn-lg px-5 py-3 font-weight-bold" id="btnOpenScanner" style="font-size: 1.5rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(255,193,7,0.4);">
        <i class="fas fa-qrcode mr-2"></i> SCAN QR PRAJURIT
    </button>
</div>

{{-- User table --}}
<div class="card" id="users-table">
    <div class="card-header">
        <h3 class="card-title">Prajurit</h3>
        <div class="card-tools d-flex align-items-center">
            <form method="GET" class="form-inline mr-2">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Cari nama">
                <button class="btn btn-sm btn-outline-primary">Filter</button>
            </form>
            
            <button type="button" class="btn btn-sm btn-success ml-2" onclick="
                if(document.querySelectorAll('.user-checkbox:checked').length === 0) {
                    alert('Pilih setidaknya satu prajurit untuk dicetak!');
                } else {
                    document.getElementById('bulk-qr-form').submit();
                }
            ">
                <i class="fas fa-print mr-1"></i> Cetak QR Massal
            </button>
        </div>
    </div>
    
    <form id="bulk-qr-form" action="{{ route('admin.jurnal-prajurit.bulk-qr') }}" method="POST" target="_blank">
        @csrf
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center" style="width:40px;">
                            <input type="checkbox" id="checkAllUsers" onclick="document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = this.checked)">
                        </th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th class="text-center">7 Hari<br><small class="text-muted">Centang</small></th>
                        <th>Terakhir Aktif</th>
                        <th class="text-center">Hari Ini</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr style="cursor:pointer;" class="kid-row" data-user-id="{{ $user->id }}" onclick="openSummaryModal({{ $user->id }})">
                        <td class="text-center" onclick="event.stopPropagation();">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox">
                        </td>
                        <td>
                            <strong>{{ $user->name }}</strong><br>
                            <small class="text-muted">{{ '@' . $user->username }}</small>
                        </td>
                        <td>{{ $user->studentProfile?->grade_class ?? '—' }}</td>
                        <td class="text-center">
                            @php $cnt = $checkCounts[$user->id] ?? 0; @endphp
                            <span class="badge badge-{{ $cnt >= 10 ? 'success' : ($cnt >= 5 ? 'warning' : 'secondary') }}">{{ $cnt }}</span>
                        </td>
                        <td>
                            @php $last = $lastEntryDates[$user->id] ?? null; @endphp
                            {{ $last ? \Carbon\Carbon::parse($last)->locale('id')->isoFormat('D MMM Y') : '—' }}
                        </td>
                        <td class="text-center">
                            @if(isset($lastEntryDates[$user->id]) && $lastEntryDates[$user->id] === $today)
                                <span class="badge badge-success"><i class="fas fa-check"></i></span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td onclick="event.stopPropagation();">
                            <a href="{{ route('admin.jurnal-prajurit.show', $user) }}" class="btn btn-xs btn-info">
                                <i class="fas fa-chart-bar"></i> Laporan
                            </a>
                            <a href="{{ route('admin.jurnal-prajurit.export', $user) }}" class="btn btn-xs btn-success">
                                <i class="fas fa-download"></i> CSV
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada pengguna Prajurit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
    @if($users->hasPages())
    <div class="card-footer">{{ $users->withQueryString()->links() }}</div>
    @endif
</div>

<hr class="my-5">

{{-- Top: Today's bible info + form window --}}
<div class="row mb-3">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-center px-3 border-right">
                        <div class="text-muted small">Hari ke</div>
                        <div class="font-weight-bold" style="font-size:2rem;line-height:1">{{ $dayNo }}</div>
                    </div>
                    <div class="flex-grow-1">
                        @if($bible)
                        <div class="font-weight-bold">{{ $bible->pl_text }} &nbsp;/&nbsp; {{ $bible->pb_text }}</div>
                        <div class="text-muted small mt-1">Jadwal pembacaan alkitab hari ini</div>
                        @else
                        <div class="text-muted">Jadwal hari ke-{{ $dayNo }} belum diisi.
                            <a href="{{ route('admin.jurnal-college.bible') }}">Atur sekarang</a>
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('admin.jurnal-college.bible') }}" class="btn btn-sm btn-outline-primary ml-3">
                        <i class="fas fa-cog"></i> Ubah Jadwal (Alkitab)
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline {{ $config->isFormOpen() ? 'card-success' : 'card-warning' }}">
            <div class="card-body py-3 text-center">
                <div class="text-muted small mb-1">Jam Form Jurnal</div>
                <div class="font-weight-bold" style="font-size:1.1rem">
                    {{ substr($config->form_open_time, 0, 5) }} – {{ substr($config->form_close_time, 0, 5) }}
                </div>
                <span class="badge badge-{{ $config->isFormOpen() ? 'success' : 'warning' }} mt-1">
                    {{ $config->isFormOpen() ? 'Sedang Buka' : 'Sedang Tutup' }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Summary stats --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalUsers }}</h3>
                <p>Total Prajurit</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('admin.jurnal-prajurit.laporan') }}" class="small-box-footer">Lihat Laporan <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $activeToday }}</h3>
                <p>Aktif Hari Ini</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#users-table" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $countMabToday }}</h3>
                <p>Hadir MA Bersama-sama</p>
            </div>
            <div class="icon"><i class="fas fa-book-reader"></i></div>
            <div class="small-box-footer">Hari Ini</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $countMasToday }}</h3>
                <p>MA di Sekolah</p>
            </div>
            <div class="icon"><i class="fas fa-school"></i></div>
            <div class="small-box-footer">Hari Ini</div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-trophy"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Tertinggi: MA Bersama-sama</span>
                <span class="info-box-number">
                    {{ $topMab ? $topMab->student->name . ' (' . $topMab->score . ' pt)' : 'Belum ada data' }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-trophy"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Tertinggi: MA di Sekolah</span>
                <span class="info-box-number">
                    {{ $topMas ? $topMas->student->name . ' (' . $topMas->score . ' pt)' : 'Belum ada data' }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-trophy"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Tertinggi: Ayat Hafalan</span>
                <span class="info-box-number">
                    {{ $topHafalan ? $topHafalan->student->name . ' (' . $topHafalan->score . ' pt)' : 'Belum ada data' }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ═══════ MODAL: QR SCANNER & JURNAL ═══════ --}}
<div class="modal fade" id="scannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content kid-modal-content">
            <div class="modal-header kid-modal-header d-flex align-items-center">
                <h5 class="modal-title kid-modal-title">
                    <i class="fas fa-qrcode mr-2"></i> Scanner & Jurnal Prajurit
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="font-size: 2rem; color: #333;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="row m-0">
                    <div class="col-md-5 p-4 bg-white border-right">
                        <div class="form-group mb-2" id="cameraSelectGroup" style="display:none;">
                            <label for="cameraSelect" class="small font-weight-bold">Pilih Kamera:</label>
                            <select id="cameraSelect" class="form-control form-control-sm"></select>
                        </div>
                        <div id="qr-reader" style="width:100%; max-width: 320px; margin: 0 auto; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);"></div>
                        <div id="scan-status" class="mt-3 text-center text-muted font-weight-bold">Arahkan QR Code Prajurit ke kamera.</div>
                    </div>
                    <div class="col-md-7 p-3 bg-light" id="scannerResultContainer">
                        <div class="text-center text-muted d-flex flex-column align-items-center justify-content-center h-100" id="scannerResultPlaceholder" style="min-height: 250px;">
                            <i class="fas fa-qrcode fa-5x mb-3" style="color: #dee2e6;"></i>
                            <h5>Menunggu Hasil Scan...</h5>
                            <p class="small">Scan QR Prajurit untuk mulai mengisi jurnal.</p>
                        </div>
                        
                        <div id="scannerResultContent" style="display:none;">
                            <div class="d-flex align-items-center mb-4 bg-white p-3 rounded shadow-sm">
                                <div id="jurnalAvatarCol" class="mr-3"></div>
                                <div>
                                    <h4 class="font-weight-bold mb-1" id="jurnalModalTitleName" style="color: #333;"></h4>
                                    <div id="jurnalPrajuritInfo" class="text-muted small"></div>
                                </div>
                            </div>
                            
                            <div id="jurnalScores" class="row text-center mb-4"></div>
                            
                            <div id="jurnalItemsList" class="mx-auto bg-white p-4 rounded shadow-sm"></div>
                            
                            <div id="jurnalSaveStatus" class="mt-3 text-center" style="font-size:1.1rem; font-weight:bold;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .kid-modal-content { border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .kid-modal-header { background: linear-gradient(135deg, #FF9A9E 0%, #FECFEF 100%); color: #333; border-bottom: none; padding: 20px 25px; }
    .kid-modal-title { font-weight: 800; font-size: 1.5rem; letter-spacing: 1px; margin: 0; }
    .kid-check-item { background: #f8f9fa; border-radius: 12px; padding: 15px; border: 2px solid #e9ecef; transition: all 0.2s; cursor: pointer; }
    .kid-check-item:hover { border-color: #a3bffa; background: #f1f5f9; transform: translateY(-2px); }
    .kid-check-item input[type="checkbox"] { transform: scale(1.5); margin-right: 15px; cursor: pointer; }
    .kid-check-label { font-size: 1.1rem; font-weight: 600; color: #495057; margin: 0; cursor: pointer; user-select: none; }
    .kid-number-input { font-size: 1.2rem; border-radius: 12px; border: 2px solid #e9ecef; font-weight: bold; }
    .kid-number-input:focus { border-color: #FF9A9E; box-shadow: 0 0 0 3px rgba(255, 154, 158, 0.3); outline: none; }
    
    @keyframes kidBounceIn {
        0% { transform: scale(0.95); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .kid-bounce { animation: kidBounceIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) both; }
</style>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const SCAN_URL  = "/admin/jurnal-prajurit/scan";
const SAVE_URL  = "/admin/jurnal-prajurit/save";
const CSRF      = "{{ csrf_token() }}";

let html5Qrcode = null;
let scanning    = false;
let currentUser = null;
let currentItems = [];
let currentDate = null;

let audioCtx = null;
function initAudio() {
    if (!audioCtx) {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (AudioContext) {
            audioCtx = new AudioContext();
        }
    }
    if (audioCtx && audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
}

function playSound(type) {
    try {
        if (!audioCtx) initAudio();
        if (!audioCtx) return;
        const osc = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        osc.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        if (type === 'success') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(800, audioCtx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
            gainNode.gain.setValueAtTime(0.5, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.2);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.2);
        } else {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(300, audioCtx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(100, audioCtx.currentTime + 0.3);
            gainNode.gain.setValueAtTime(0.5, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.3);
        }
    } catch(e) {}
}

function startScanner(cameraIdOrConfig) {
    try {
        if (html5Qrcode) {
            let state = 0;
            try { state = html5Qrcode.getState(); } catch(e) {}
            
            if (state === 2) { // SCANNING
                html5Qrcode.stop().then(() => {
                    try { html5Qrcode.clear(); } catch(e) {}
                    initScanner(cameraIdOrConfig);
                }).catch(() => {
                    initScanner(cameraIdOrConfig);
                });
            } else {
                try { html5Qrcode.clear(); } catch(e) {}
                initScanner(cameraIdOrConfig);
            }
        } else {
            initScanner(cameraIdOrConfig);
        }
    } catch(e) {
        initScanner(cameraIdOrConfig);
    }
}

function initScanner(cameraIdOrConfig, isFallback = false) {
    html5Qrcode = new Html5Qrcode("qr-reader");
    html5Qrcode.start(
        cameraIdOrConfig,
        {
            fps: 10,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                let minEdgePercentage = 0.7;
                let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                return { width: qrboxSize, height: qrboxSize };
            },
            aspectRatio: 1.0
        },
        onScanSuccess,
        (errorMessage) => { /* ignore */ }
    ).then(() => {
        let msg = document.getElementById('scan-status');
        if(msg) msg.innerHTML = 'Arahkan QR Code ke kamera.';
    }).catch(err => {
        if (!isFallback && err.toString().includes('OverconstrainedError')) {
            if (html5Qrcode) {
                try { html5Qrcode.clear(); } catch(e) {}
            }
            let select = document.getElementById('cameraSelect');
            if (select) select.value = 'user';
            initScanner({ facingMode: "user" }, true);
        } else {
            let msg = document.getElementById('scan-status');
            if(msg) msg.innerHTML = '<span class="text-danger text-red-500">Gagal memulai kamera: ' + err + '</span>';
        }
    });
}

document.getElementById('cameraSelect').addEventListener('change', function() {
    let val = this.value;
    if (val === 'environment' || val === 'user') {
        startScanner({ facingMode: val });
    } else {
        startScanner(val); // By device ID
    }
});

document.getElementById('btnOpenScanner').addEventListener('click', () => {
    initAudio();
    $('#scannerModal').modal('show');
    document.getElementById('scan-status').innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin mr-1"></i>Meminta akses kamera...</span>';
    
    Html5Qrcode.getCameras().then(devices => {
        let select = document.getElementById('cameraSelect');
        select.innerHTML = '';
        if (devices && devices.length > 0) {
            devices.forEach((device, index) => {
                let option = document.createElement('option');
                option.value = device.id;
                option.text = device.label || `Kamera ${index + 1}`;
                select.appendChild(option);
            });
            // Auto select back camera if possible, otherwise first camera
            let backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('belakang'));
            if (backCam) {
                select.value = backCam.id;
            }
        } else {
            select.innerHTML = `
                <option value="environment">Kamera Belakang (Default)</option>
                <option value="user">Kamera Depan</option>
            `;
        }
        document.getElementById('cameraSelectGroup').style.display = 'block';

        setTimeout(() => {
            if (!$('#scannerModal').hasClass('show')) return;
            
            let val = select.value;
            if (val === 'environment' || val === 'user') {
                startScanner({ facingMode: val });
            } else {
                startScanner(val);
            }
        }, 400); 
    }).catch(err => {
        document.getElementById('scan-status').innerHTML = '<span class="text-danger">Izin kamera ditolak/gagal. Pastikan browser mengizinkan akses kamera (cek setelan situs). (' + err + ')</span>';
    });
});

$('#scannerModal').on('hide.bs.modal', function () {
    if (html5Qrcode) {
        try {
            html5Qrcode.stop().then(() => {
                html5Qrcode.clear();
                html5Qrcode = null;
            }).catch(e => {
                html5Qrcode.clear();
                html5Qrcode = null;
            });
        } catch (e) {
            html5Qrcode = null;
        }
    }
    if (shouldReloadOnClose) {
        location.reload();
    }
    
    // Reset view
    document.getElementById('scannerResultContent').style.display = 'none';
    document.getElementById('scannerResultPlaceholder').classList.remove('d-none');
    document.getElementById('scannerResultPlaceholder').classList.add('d-flex');
});

let shouldReloadOnClose = false;

function onScanSuccess(decodedText) {
    if (scanning) return;
    scanning = true;

    const userId = parseInt(decodedText.trim(), 10);
    if (isNaN(userId)) {
        playSound('error');
        document.getElementById('scan-status').innerHTML =
            '<span class="text-danger">QR tidak valid</span>';
        setTimeout(() => { scanning = false; }, 2000);
        return;
    }

    document.getElementById('scan-status').innerHTML =
        '<span class="text-info"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat data...</span>';

    fetch(SCAN_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ user_id: userId }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'not_found') {
            playSound('error');
            document.getElementById('scan-status').innerHTML =
                '<span class="text-danger">Prajurit tidak ditemukan atau tidak aktif.</span>';
            setTimeout(() => { scanning = false; }, 2500);
            return;
        }

        playSound('success');
        currentUser  = data.prajurit;
        currentItems = data.items;
        currentDate  = data.today;
        
        document.getElementById('scan-status').innerHTML =
            '<span class="text-success"><i class="fas fa-check mr-1"></i>Berhasil discan! Lanjut scan QR lain jika perlu.</span>';

        // Render data di kolom kanan tanpa menutup scanner
        openJurnalModal(data);
        
        // Timeout sebelum mengizinkan scan baru (mencegah double scan cepat)
        setTimeout(() => { scanning = false; }, 1500);
    })
    .catch(() => {
        playSound('error');
        document.getElementById('scan-status').innerHTML =
            '<span class="text-danger">Koneksi gagal.</span>';
        setTimeout(() => { scanning = false; }, 2000);
    });
}

function openJurnalModal(data) {
    document.getElementById('scannerResultPlaceholder').classList.remove('d-flex');
    document.getElementById('scannerResultPlaceholder').classList.add('d-none');
    
    const contentDiv = document.getElementById('scannerResultContent');
    contentDiv.style.display = 'block';
    contentDiv.classList.remove('kid-bounce');
    void contentDiv.offsetWidth; // trigger reflow
    contentDiv.classList.add('kid-bounce');

    document.getElementById('jurnalModalTitleName').innerHTML = escHtml(data.prajurit.name);
        
    let avatarUrl = data.prajurit.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.prajurit.name)+'&size=150&background=FF9A9E&color=fff';
    
    document.getElementById('jurnalAvatarCol').innerHTML = `
        <img src="${avatarUrl}" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #FF9A9E;" alt="Foto Profil">
    `;

    let kelasHtml = data.prajurit.kelas ? `Kelas: <strong>${escHtml(data.prajurit.kelas)}</strong> <span class="mx-2">|</span>` : `<span class="text-black-50">Kelas: Belum diatur</span> <span class="mx-2">|</span>`;
    document.getElementById('jurnalPrajuritInfo').innerHTML =
        `${kelasHtml} Tanggal: <strong>${data.today_formatted || data.today}</strong>`;

    let scoresHtml = '';
    if (data.prajurit.scores) {
        scoresHtml = `
            <div class="col-4">
                <div class="p-2 border rounded shadow-sm bg-white">
                    <div class="small text-muted font-weight-bold">MAB</div>
                    <div class="h4 mb-0 text-primary">${data.prajurit.scores.mab || 0}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 border rounded shadow-sm bg-white">
                    <div class="small text-muted font-weight-bold">MAS</div>
                    <div class="h4 mb-0 text-success">${data.prajurit.scores.mas || 0}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 border rounded shadow-sm bg-white">
                    <div class="small text-muted font-weight-bold">Hafalan</div>
                    <div class="h4 mb-0 text-warning">${data.prajurit.scores.hafalan || 0}</div>
                </div>
            </div>
        `;
    }
    document.getElementById('jurnalScores').innerHTML = scoresHtml;

    let html = '';
    data.items.forEach(item => {
        const checked = data.checkedIds.includes(item.id);
        if (item.response_type === 'boolean') {
            html += `
            <label class="kid-check-item d-flex align-items-center mb-3" for="item_${item.id}">
                <input type="checkbox" class="jurnal-check"
                    id="item_${item.id}" data-item-id="${item.id}" data-type="boolean"
                    ${checked ? 'checked' : ''}>
                <span class="kid-check-label">${escHtml(item.label)}</span>
            </label>`;
        } else {
            const val = data.numberValues[item.id] ?? '';
            const displayVal = (val === 0 || val === '0') ? '' : val;
            html += `
            <div class="kid-check-item d-flex align-items-center justify-content-between mb-3">
                <label for="item_num_${item.id}" class="kid-check-label m-0">${escHtml(item.label)}</label>
                <input type="number" min="0" class="kid-number-input jurnal-number m-0 text-center"
                    id="item_num_${item.id}" data-item-id="${item.id}" data-type="number"
                    value="${displayVal}" placeholder="" style="width: 80px; padding: 5px 10px;">
            </div>`;
        }
    });

    document.getElementById('jurnalItemsList').innerHTML = html;
    document.getElementById('jurnalSaveStatus').innerHTML = '';
    
    // Attach autosave listeners
    document.querySelectorAll('.jurnal-check').forEach(el => {
        el.addEventListener('change', () => autoSaveJurnal());
    });
    
    let debounceTimer;
    document.querySelectorAll('.jurnal-number').forEach(el => {
        el.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => autoSaveJurnal(), 500);
        });
    });
}

function autoSaveJurnal() {
    const checks = [];

    document.querySelectorAll('.jurnal-check').forEach(el => {
        checks.push({
            item_id: parseInt(el.dataset.itemId),
            checked: el.checked,
            value: null,
        });
    });

    document.querySelectorAll('.jurnal-number').forEach(el => {
        checks.push({
            item_id: parseInt(el.dataset.itemId),
            checked: (el.value > 0),
            value: parseInt(el.value) || 0,
        });
    });

    document.getElementById('jurnalSaveStatus').innerHTML =
        '<span class="text-info"><i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...</span>';

    fetch(SAVE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            user_id: currentUser.id,
            tanggal: currentDate,
            checks: checks,
        }),
    })
    .then(r => {
        if (!r.ok) throw new Error('Network error');
        return r.json();
    })
    .then(data => {
        if (data.status === 'saved') {
            document.getElementById('jurnalSaveStatus').innerHTML =
                '<span class="text-success"><i class="fas fa-check mr-1"></i>Tersimpan otomatis</span>';
            shouldReloadOnClose = true;
        } else {
            document.getElementById('jurnalSaveStatus').innerHTML =
                '<span class="text-danger">Respon tidak valid</span>';
        }
    })
    .catch(() => {
        document.getElementById('jurnalSaveStatus').innerHTML =
            '<span class="text-danger">Gagal menyimpan otomatis.</span>';
    });
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
<style>
    .kid-summary-modal .modal-content {
        border-radius: 25px;
        border: none;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        background: #fdfbfb;
    }
    .kid-summary-header {
        background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
        color: #fff;
        border-bottom: none;
        padding: 25px 30px;
        position: relative;
    }
    .kid-summary-header::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 0;
        right: 0;
        height: 30px;
        background: #fdfbfb;
        border-radius: 50% 50% 0 0;
    }
    .kid-summary-title {
        font-weight: 900;
        font-size: 1.8rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        z-index: 1;
        position: relative;
    }
    .kid-timeline {
        position: relative;
        padding: 20px 0;
        margin-left: 20px;
    }
    .kid-timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 20px;
        width: 4px;
        background: #e0eaf5;
        border-radius: 2px;
    }
    .kid-timeline-item {
        position: relative;
        margin-bottom: 25px;
        padding-left: 50px;
    }
    .kid-timeline-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #fff;
        border: 4px solid #8fd3f4;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #8fd3f4;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        z-index: 1;
    }
    .kid-timeline-content {
        background: #fff;
        border-radius: 15px;
        padding: 15px 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border: 2px solid transparent;
        transition: all 0.3s;
    }
    .kid-timeline-content:hover {
        border-color: #84fab0;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .kid-timeline-date {
        font-weight: 800;
        color: #555;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }
    .kid-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: bold;
        margin-right: 5px;
        margin-bottom: 5px;
    }
    .kid-badge-yes { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .kid-badge-no { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; text-decoration: line-through; opacity: 0.7;}
    
    @keyframes rowHover {
        0% { background: #fff; }
        100% { background: #f0fdf4; }
    }
    .kid-row:hover {
        animation: rowHover 0.3s forwards;
    }
</style>

<div class="modal fade" id="summaryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content kid-summary-modal">
            <div class="modal-header kid-summary-header d-flex flex-column align-items-center">
                <div style="position:relative; width: 100%; text-align: center;">
                    <div style="font-size: 4rem; margin-bottom: -10px;">🚀</div>
                    <h5 class="modal-title kid-summary-title" id="summaryModalTitle">
                        Jurnal Petualangan
                    </h5>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" style="position: absolute; top: 15px; right: 20px; font-size: 2rem; opacity: 1;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="max-height: 60vh; overflow-y: auto;">
                <div class="text-center mb-4" id="summaryUserInfo">
                    <!-- User info -->
                </div>
                <div class="kid-timeline" id="summaryTimeline">
                    <!-- Timeline items injected via JS -->
                </div>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <button type="button" class="btn btn-lg px-5" data-dismiss="modal" style="border-radius: 30px; background: #8fd3f4; color: #fff; font-weight: bold; box-shadow: 0 4px 15px rgba(143,211,244,0.4);">
                    Tutup Petualangan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openSummaryModal(userId) {
    const url = `/admin/jurnal-prajurit/summary/${userId}`;
    
    document.getElementById('summaryUserInfo').innerHTML = '<div class="spinner-border text-info" role="status"></div><p class="mt-2 text-muted">Memuat data petualangan...</p>';
    document.getElementById('summaryTimeline').innerHTML = '';
    
    $('#summaryModal').modal('show');
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        let avatarUrl = data.user.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.user.name)+'&size=150&background=8fd3f4&color=fff';
        
        let userInfoHtml = `
            <img src="${avatarUrl}" class="rounded-circle shadow" style="width:100px; height:100px; object-fit:cover; border: 4px solid #84fab0;" alt="Foto Profil">
            <h4 class="mt-3 font-weight-bold" style="color: #333;">${escHtml(data.user.name)}</h4>
            <span class="badge badge-primary" style="font-size:1rem; border-radius:15px; padding: 5px 15px;">Kelas: ${escHtml(data.user.kelas || '—')}</span>
        `;
        document.getElementById('summaryUserInfo').innerHTML = userInfoHtml;
        
        const matrix = data.matrix;
        const headers = matrix.headers; // ["Tanggal", "PL", "PB", ...]
        const rows = matrix.rows.slice().reverse(); // Reverse to show latest first
        
        let timelineHtml = '';
        
        rows.forEach((row, i) => {
            const dateStr = row[0]; // YYYY-MM-DD
            const d = new Date(dateStr);
            const formattedDate = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            
            let badgesHtml = '';
            let checkedCount = 0;
            let totalItems = headers.length - 1;
            
            for(let j = 1; j < headers.length; j++) {
                const label = headers[j];
                const cellStr = String(row[j]);
                const checked = cellStr.startsWith('Y');
                if(checked) checkedCount++;
                
                if (checked) {
                    let valStr = '';
                    if (cellStr.includes(':')) {
                        valStr = ' <span class="badge badge-light ml-1 text-dark">' + cellStr.split(':')[1] + '</span>';
                    }
                    badgesHtml += `<span class="kid-badge kid-badge-yes"><i class="fas fa-check mr-1"></i> ${escHtml(label)}${valStr}</span>`;
                } else {
                    badgesHtml += `<span class="kid-badge kid-badge-no"><i class="fas fa-times mr-1"></i> ${escHtml(label)}</span>`;
                }
            }
            
            let iconStr = checkedCount === totalItems ? '🌟' : (checkedCount > 0 ? '👍' : '💤');
            
            timelineHtml += `
                <div class="kid-timeline-item">
                    <div class="kid-timeline-icon">${iconStr}</div>
                    <div class="kid-timeline-content">
                        <div class="kid-timeline-date">${formattedDate}</div>
                        <div>${badgesHtml}</div>
                    </div>
                </div>
            `;
        });
        
        if (timelineHtml === '') {
            timelineHtml = '<div class="text-center text-muted">Belum ada petualangan dicatat.</div>';
        }
        
        document.getElementById('summaryTimeline').innerHTML = timelineHtml;
    })
    .catch(err => {
        document.getElementById('summaryUserInfo').innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat data.</div>';
    });
}
</script>
@endpush
@if(request()->query('scan') == '1')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            var btn = document.getElementById('btnOpenScanner');
            if (btn) btn.click();
        }, 500);
    });
</script>
@endif
@endsection
