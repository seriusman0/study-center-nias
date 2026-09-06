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
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalUsers }}</h3>
                <p>Total Prajurit</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('admin.jurnal-prajurit.laporan') }}" class="small-box-footer">Lihat Laporan <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $activeToday }}</h3>
                <p>Aktif Hari Ini</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#users-table" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-{{ $activeToday > 0 ? 'primary' : 'secondary' }}">
            <div class="inner">
                <h3>{{ $totalUsers > 0 ? round($activeToday / $totalUsers * 100) : 0 }}%</h3>
                <p>Keaktifan Hari Ini</p>
            </div>
            <div class="icon"><i class="fas fa-chart-pie"></i></div>
            <a href="{{ route('admin.jurnal-college.bible') }}" class="small-box-footer">Pengaturan Alkitab <i class="fas fa-arrow-circle-right"></i></a>
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

{{-- ═══════ MODAL: JURNAL PRAJURIT ═══════ --}}
<div class="modal fade" id="jurnalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="jurnalModalTitle">
                    <i class="fas fa-book-open mr-1"></i> Jurnal Prajurit
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="jurnalModalBody">
                <div class="text-muted small mb-3" id="jurnalPrajuritInfo"></div>
                <div id="jurnalItemsList"></div>
                <div id="jurnalSaveStatus" class="mt-2 text-center"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnSaveJurnal">
                    <i class="fas fa-save mr-1"></i> Simpan & Tutup
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

function onScanSuccess(decodedText) {
    if (scanning) return;
    scanning = true;

    const userId = parseInt(decodedText.trim(), 10);
    if (isNaN(userId)) {
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
            document.getElementById('scan-status').innerHTML =
                '<span class="text-danger">Prajurit tidak ditemukan atau tidak aktif.</span>';
            setTimeout(() => { scanning = false; }, 2500);
            return;
        }

        currentUser  = data.prajurit;
        currentItems = data.items;

        $('#scannerModal').modal('hide');
        setTimeout(() => openJurnalModal(data), 500); // Wait for modal animation
        scanning = false;
    })
    .catch(() => {
        document.getElementById('scan-status').innerHTML =
            '<span class="text-danger">Koneksi gagal.</span>';
        setTimeout(() => { scanning = false; }, 2000);
    });
}

function openJurnalModal(data) {
    document.getElementById('jurnalModalTitle').innerHTML =
        `<i class="fas fa-book-open mr-1"></i> Jurnal — ${escHtml(data.prajurit.name)}`;
    document.getElementById('jurnalPrajuritInfo').innerHTML =
        `Kelas: <strong>${escHtml(data.prajurit.kelas || '—')}</strong> &nbsp;·&nbsp; Tanggal: <strong>${data.today}</strong>`;

    let html = '';
    data.items.forEach(item => {
        const checked = data.checkedIds.includes(item.id);
        if (item.response_type === 'boolean') {
            html += `
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input jurnal-check"
                    id="item_${item.id}" data-item-id="${item.id}" data-type="boolean"
                    ${checked ? 'checked' : ''}>
                <label class="custom-control-label" for="item_${item.id}">
                    ${escHtml(item.label)}
                </label>
            </div>`;
        } else {
            const val = data.numberValues[item.id] ?? '';
            html += `
            <div class="form-group mb-2">
                <label for="item_num_${item.id}">${escHtml(item.label)}</label>
                <input type="number" min="0" class="form-control jurnal-number"
                    id="item_num_${item.id}" data-item-id="${item.id}" data-type="number"
                    value="${val}" placeholder="0">
            </div>`;
        }
    });
    document.getElementById('jurnalItemsList').innerHTML = html;
    document.getElementById('jurnalSaveStatus').innerHTML = '';
    $('#jurnalModal').modal('show');
}

document.getElementById('btnSaveJurnal').addEventListener('click', saveJurnal);

function saveJurnal() {
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
            tanggal: document.getElementById('jurnalPrajuritInfo')
                .textContent.match(/\d{4}-\d{2}-\d{2}/)?.[0],
            checks: checks,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'saved') {
            $('#jurnalModal').modal('hide');
            location.reload();
        }
    })
    .catch(() => {
        document.getElementById('jurnalSaveStatus').innerHTML =
            '<span class="text-danger">Gagal menyimpan. Coba lagi.</span>';
    });
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
@endpush
@endsection
