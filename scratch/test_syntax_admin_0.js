
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
