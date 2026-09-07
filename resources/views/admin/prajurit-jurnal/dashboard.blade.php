@extends('layouts.admin')
@section('page-title', 'Jurnal Prajurit')

@section('content')

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

{{-- User table --}}
<div class="card" id="users-table">
    <div class="card-header">
        <h3 class="card-title">Prajurit</h3>
        <div class="card-tools d-flex align-items-center">
            <form method="GET" class="form-inline">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Cari nama">
                <button class="btn btn-sm btn-outline-primary">Filter</button>
            </form>
            <button type="button" class="btn btn-sm btn-warning ml-2" id="btnOpenScanner">
                <i class="fas fa-qrcode mr-1"></i> Scan QR Prajurit
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
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
                <tr>
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
                    <td>
                        <a href="{{ route('admin.jurnal-prajurit.show', $user) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                        <a href="{{ route('admin.jurnal-prajurit.export', $user) }}" class="btn btn-xs btn-success">
                            <i class="fas fa-download"></i> CSV
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada pengguna Remaja Beasiswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer">{{ $users->withQueryString()->links() }}</div>
    @endif
</div>
{{-- ═══════ MODAL: QR SCANNER ═══════ --}}
<div class="modal fade" id="scannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode mr-1"></i> Scan QR Prajurit
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="qr-reader" style="width:100%"></div>
                <div id="scan-status" class="mt-2 text-center text-muted small"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .kid-modal-content { border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .kid-modal-header { background: linear-gradient(135deg, #FF9A9E 0%, #FECFEF 100%); color: #333; border-bottom: none; padding: 20px 25px; }
    .kid-modal-title { font-weight: 800; font-size: 1.5rem; letter-spacing: 1px; }
    .kid-avatar-container { width: 100%; padding-top: 100%; position: relative; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border: 4px solid #fff; }
    .kid-avatar { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
    .kid-check-item { background: #f8f9fa; border-radius: 12px; padding: 15px; margin-bottom: 12px; border: 2px solid #e9ecef; transition: all 0.2s; cursor: pointer; }
    .kid-check-item:hover { border-color: #a3bffa; background: #f1f5f9; transform: translateY(-2px); }
    .kid-check-item input[type="checkbox"] { transform: scale(1.5); margin-right: 15px; cursor: pointer; }
    .kid-check-label { font-size: 1.1rem; font-weight: 600; color: #495057; margin: 0; cursor: pointer; user-select: none; }
    .kid-number-input { font-size: 1.2rem; padding: 10px 15px; border-radius: 12px; border: 2px solid #e9ecef; width: 100%; text-align: center; font-weight: bold; }
    .kid-number-input:focus { border-color: #FF9A9E; box-shadow: 0 0 0 3px rgba(255, 154, 158, 0.3); outline: none; }
    .kid-btn-save { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border: none; color: #fff; font-weight: bold; font-size: 1.2rem; padding: 12px 30px; border-radius: 50px; box-shadow: 0 4px 15px rgba(67, 233, 123, 0.4); transition: all 0.3s; }
    .kid-btn-save:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(67, 233, 123, 0.6); color: #fff; }
    
    @keyframes kidBounceIn {
        0% { transform: scale(0.8); opacity: 0; }
        60% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    .kid-bounce { animation: kidBounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) both; }
</style>

{{-- ═══════ MODAL: JURNAL PRAJURIT ═══════ --}}
<div class="modal fade" id="jurnalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content kid-modal-content">
            <div class="modal-header kid-modal-header d-flex align-items-center">
                <h5 class="modal-title kid-modal-title" id="jurnalModalTitle">
                    <i class="fas fa-star text-warning mr-2"></i> Jurnal Prajurit
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="font-size: 2rem; color: #333;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="jurnalModalBody">
                <div class="row">
                    <div class="col-md-4 text-center mb-4 mb-md-0" id="jurnalAvatarCol">
                        <!-- Avatar -->
                    </div>
                    <div class="col-md-8">
                        <div class="bg-light rounded p-3 mb-3 text-center" id="jurnalPrajuritInfo" style="font-size:1.1rem;">
                            <!-- Info -->
                        </div>
                        <div id="jurnalItemsList"></div>
                        <div id="jurnalSaveStatus" class="mt-3 text-center" style="font-size:1.1rem; font-weight:bold;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn kid-btn-save" id="btnSaveJurnal">
                    <i class="fas fa-check-circle mr-2"></i> Simpan Jurnal
                </button>
            </div>
        </div>
    </div>
</div>

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

function playSound(type) {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        const ctx = new AudioContext();
        const osc = ctx.createOscillator();
        const gainNode = ctx.createGain();
        osc.connect(gainNode);
        gainNode.connect(ctx.destination);
        if (type === 'success') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(800, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.1);
            gainNode.gain.setValueAtTime(0.5, ctx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.2);
        } else {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(300, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(100, ctx.currentTime + 0.3);
            gainNode.gain.setValueAtTime(0.5, ctx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.3);
        }
    } catch(e) {}
}

document.getElementById('btnOpenScanner').addEventListener('click', () => {
    $('#scannerModal').modal('show');
    document.getElementById('scan-status').innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin mr-1"></i>Meminta akses kamera...</span>';
    
    // Request permission synchronously inside the click event to avoid NotAllowedError on mobile browsers
    Html5Qrcode.getCameras().then(devices => {
        // Wait briefly for modal transition to complete so dimensions are available
        setTimeout(() => {
            if (!$('#scannerModal').hasClass('show')) return; // Check if user closed modal while granting permission
            
            if (devices && devices.length) {
                html5Qrcode = new Html5Qrcode("qr-reader");
                html5Qrcode.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    onScanSuccess,
                    (errorMessage) => { /* ignore */ }
                ).then(() => {
                    document.getElementById('scan-status').innerHTML = 'Arahkan QR Code Prajurit ke kamera.';
                }).catch(err => {
                    document.getElementById('scan-status').innerHTML = '<span class="text-danger">Gagal memulai kamera: ' + err + '</span>';
                });
            } else {
                document.getElementById('scan-status').innerHTML = '<span class="text-danger">Kamera tidak ditemukan pada perangkat ini.</span>';
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
});

let shouldReloadOnClose = false;
$('#jurnalModal').on('hide.bs.modal', function () {
    if (shouldReloadOnClose) {
        location.reload();
    }
});

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

        $('#scannerModal').modal('hide');
        setTimeout(() => openJurnalModal(data), 500); // Wait for modal animation
        scanning = false;
    })
    .catch(() => {
        playSound('error');
        document.getElementById('scan-status').innerHTML =
            '<span class="text-danger">Koneksi gagal.</span>';
        setTimeout(() => { scanning = false; }, 2000);
    });
}

function openJurnalModal(data) {
    document.getElementById('jurnalModalTitle').innerHTML =
        `<i class="fas fa-star text-warning mr-2"></i> Halo, ${escHtml(data.prajurit.name)}!`;
        
    let avatarUrl = data.prajurit.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.prajurit.name)+'&size=300&background=FF9A9E&color=fff';
    
    document.getElementById('jurnalAvatarCol').innerHTML = `
        <div class="kid-avatar-container text-center mb-3">
            <img src="${avatarUrl}" class="kid-avatar rounded-circle border border-3 border-primary" style="width: 100px; height: 100px; object-fit: cover;" alt="Foto Profil">
        </div>
    `;

    document.getElementById('jurnalPrajuritInfo').innerHTML =
        `Kelas: <strong>${escHtml(data.prajurit.kelas || '—')}</strong> &nbsp;|&nbsp; 
         Tanggal: <strong>${data.today_formatted || data.today}</strong>`;

    let html = '';
    data.items.forEach(item => {
        const checked = data.checkedIds.includes(item.id);
        if (item.response_type === 'boolean') {
            html += `
            <label class="kid-check-item d-flex align-items-center" for="item_${item.id}">
                <input type="checkbox" class="jurnal-check"
                    id="item_${item.id}" data-item-id="${item.id}" data-type="boolean"
                    ${checked ? 'checked' : ''}>
                <span class="kid-check-label">${escHtml(item.label)}</span>
            </label>`;
        } else {
            const val = data.numberValues[item.id] ?? '';
            html += `
            <div class="kid-check-item text-center">
                <label for="item_num_${item.id}" class="kid-check-label mb-2 d-block">${escHtml(item.label)}</label>
                <input type="number" min="0" class="kid-number-input jurnal-number"
                    id="item_num_${item.id}" data-item-id="${item.id}" data-type="number"
                    value="${val}" placeholder="0">
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

    shouldReloadOnClose = false;
    $('#jurnalModal').modal('show');
    $('.kid-modal-content').removeClass('kid-bounce');
    // Trigger reflow to restart animation
    if(document.querySelector('.kid-modal-content')) {
        void document.querySelector('.kid-modal-content').offsetWidth;
        $('.kid-modal-content').addClass('kid-bounce');
    }
}

document.getElementById('btnSaveJurnal').addEventListener('click', () => {
    $('#jurnalModal').modal('hide');
});

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
@endpush
@endsection
