

let html5Qrcode = null;
let scanning    = false;
let currentUser = null;
let currentItems = [];
let currentDate = null;

const PUBLIC_SCAN_URL  = "/public-jurnal/scan";
const PUBLIC_SAVE_URL  = "/public-jurnal/save";
const CSRF             = "{{ csrf_token() }}";

function openPublicScanner() {
    try {
        document.getElementById('publicScannerModal').classList.remove('hidden');
        document.getElementById('scan-status').innerHTML = '<span class="text-blue-500">Meminta akses kamera...</span>';
        
        Html5Qrcode.getCameras().then(devices => {
            let select = document.getElementById('cameraSelect');
            select.innerHTML = '';
            if (devices && devices.length > 0) {
                let backCam = devices.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('belakang'));
                let defaultId = backCam ? backCam.id : devices[0].id;
                
                devices.forEach(cam => {
                    let opt = document.createElement('option');
                    opt.value = cam.id;
                    opt.text = cam.label || 'Kamera ' + cam.id;
                    select.appendChild(opt);
                });
                
                select.value = defaultId;

                setTimeout(() => {
                    if (document.getElementById('publicScannerModal').classList.contains('hidden')) return;
                    startScanner(defaultId);
                }, 400); 
            } else {
                select.innerHTML = '<option value="environment">Kamera Belakang</option><option value="user">Kamera Depan</option>';
                setTimeout(() => {
                    if (document.getElementById('publicScannerModal').classList.contains('hidden')) return;
                    startScanner({ facingMode: "environment" });
                }, 400); 
            }
        }).catch(err => {
            document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Izin kamera ditolak/gagal. Pastikan browser mengizinkan akses kamera. (' + err + ')</span>';
        });
    } catch(e) {
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Error sistem: ' + e.message + '</span>';
    }
}

document.getElementById('cameraSelect').addEventListener('change', function() {
    let val = this.value;
    if (val === 'environment' || val === 'user') {
        startScanner({ facingMode: val });
    } else {
        startScanner(val);
    }
});

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
            }
        },
        onScanSuccess,
        (errorMessage) => { /* ignore */ }
    ).then(() => {
        let msg = document.getElementById('scan-status');
        if(msg) msg.innerHTML = 'Arahkan QR Code ke kamera.';
    }).catch(err => {
        if (!isFallback && err.toString().includes('OverconstrainedError')) {
            if (html5Qrcode) { try { html5Qrcode.clear(); } catch(e){} }
            let select = document.getElementById('cameraSelect');
            if (select) select.value = 'user';
            initScanner({ facingMode: "user" }, true);
        } else {
            let msg = document.getElementById('scan-status');
            if(msg) msg.innerHTML = '<span class="text-danger text-red-500">Gagal memulai kamera: ' + err + '</span>';
        }
    });
}

function startScanner(cameraIdOrConfig) {
    try {
        if (html5Qrcode) {
            let state = 0;
            try { state = html5Qrcode.getState(); } catch(e) {}
            if (state === 2) {
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

function closePublicScanner() {
    document.getElementById('publicScannerModal').classList.add('hidden');
    if (html5Qrcode) {
        try {
            html5Qrcode.stop().then(() => {
                try { html5Qrcode.clear(); } catch(e) {}
                html5Qrcode = null;
            }).catch(() => { html5Qrcode = null; });
        } catch(e) {}
    }
    
}

function onScanSuccess(decodedText) {
    if (scanning) return;
    scanning = true;

    const userId = parseInt(decodedText.trim(), 10);
    if (isNaN(userId)) {
        playSound('error');
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">QR tidak valid</span>';
        setTimeout(() => { scanning = false; }, 2000);
        return;
    }

    document.getElementById('scan-status').innerHTML = '<span class="text-blue-500">Memuat data...</span>';

    fetch(PUBLIC_SCAN_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ user_id: userId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'not_found') {
            playSound('error');
            document.getElementById('scan-status').innerHTML = '<span class="text-red-500">' + (data.message || 'Pengguna tidak ditemukan/tidak aktif.') + '</span>';
            setTimeout(() => { scanning = false; }, 2500);
            return;
        }
        if (data.status === 'redirect') {
            playSound('success');
            document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">Berhasil! Mengalihkan...</span>';
            
            try {
                if (html5Qrcode) {
                    html5Qrcode.stop().then(() => {
                        window.location.replace(data.url);
                    }).catch(() => {
                        window.location.replace(data.url);
                    });
                } else {
                    window.location.replace(data.url);
                }
            } catch (e) {
                window.location.replace(data.url);
            }
            return;
        }
        


        playSound('error');
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Gagal mengalihkan.</span>';
        setTimeout(() => { scanning = false; }, 2000);
    })
    .catch(() => {
        playSound('error');
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Koneksi gagal.</span>';
        setTimeout(() => { scanning = false; }, 2000);
    });
}



function playSound(type) {
    // optional audio
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>'"]/g, match => {
        return {
            '&': '&amp;', '<': '&lt;', '>': '&gt;',
            "'": '&#39;', '"': '&quot;'
        }[match];
    });
}

