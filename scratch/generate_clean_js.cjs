const js = `
let html5Qrcode = null;
let scanning    = false;
let currentUser = null;
let currentItems = [];
let currentDate = null;

const PUBLIC_SCAN_URL  = "{{ route('public-jurnal.scan') }}";
const PUBLIC_SAVE_URL  = "{{ route('public-jurnal.save') }}";
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

function resetScannerUI() {
    document.getElementById('scannerResultContainer').classList.add('hidden');
    document.getElementById('scannerCameraSection').classList.remove('hidden');
    document.getElementById('scan-status').innerHTML = '<span class="text-gray-600">Arahkan QR Code ke kamera.</span>';
    if(html5Qrcode && html5Qrcode.getState() !== 2) {
        let select = document.getElementById('cameraSelect');
        if(select && select.value) {
            startScanner(select.value);
        }
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
    document.getElementById('scannerResultContainer').classList.add('hidden');
    document.getElementById('scannerCameraSection').classList.remove('hidden');
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
            document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Pengguna tidak ditemukan/tidak aktif.</span>';
            setTimeout(() => { scanning = false; }, 2500);
            return;
        }
        if (data.status === 'no_items') {
            playSound('error');
            document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Tidak ada item jurnal.</span>';
            setTimeout(() => { scanning = false; }, 2500);
            return;
        }

        playSound('success');
        currentUser = data.user;
        currentItems = data.items;
        currentDate = data.today;
        
        document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">Berhasil!</span>';
        openPublicJurnalModal(data);
        setTimeout(() => { scanning = false; }, 1500);
    })
    .catch(() => {
        playSound('error');
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Koneksi gagal.</span>';
        setTimeout(() => { scanning = false; }, 2000);
    });
}

function openPublicJurnalModal(data) {
    document.getElementById('scannerCameraSection').classList.add('hidden');
    document.getElementById('scannerResultContainer').classList.remove('hidden');

    document.getElementById('jurnalModalTitleName').innerHTML = escHtml(data.user.name);
    let avatarUrl = data.user.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.user.name)+'&size=150&background=0F766E&color=fff';
    
    document.getElementById('jurnalAvatarCol').innerHTML = \`<img src="\${avatarUrl}" class="rounded-full shadow-sm object-cover border-4 border-sc-teal-200" style="width: 70px; height: 70px;" alt="Foto Profil">\`;

    let kelasHtml = data.user.kelas ? \`Kelas/Info: <strong>\${escHtml(data.user.kelas)}</strong> <span class="mx-2 text-gray-300">|</span>\` : \`\`;
    document.getElementById('jurnalPrajuritInfo').innerHTML = \`\${kelasHtml} Tanggal: <strong>\${data.today_formatted || data.today}</strong>\`;

    const container = document.getElementById('jurnalChecklistContainer');
    container.innerHTML = '';

    let checkedIds = data.checkedIds || [];
    let numberValues = data.numberValues || {};

    data.items.forEach(item => {
        let isChecked = checkedIds.includes(item.id);
        
        let card = document.createElement('div');
        card.className = "flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl hover:shadow-sm transition cursor-pointer group";
        
        let valInput = '';
        if (item.tipe === 'number') {
            let curVal = numberValues[item.id] || '';
            valInput = \`
                <div class="mt-2" onclick="event.stopPropagation();">
                    <input type="number" id="val_\${item.id}" value="\${curVal}" class="form-input w-24 text-sm border-gray-300 rounded-lg shadow-sm focus:ring-sc-teal-500 focus:border-sc-teal-500 py-1" placeholder="Nilai">
                    <button type="button" onclick="saveJurnalPublic(\${item.id}, 'number')" class="ml-2 px-3 py-1 bg-sc-teal-600 text-white text-xs font-bold rounded-lg hover:bg-sc-teal-700">Simpan</button>
                </div>
            \`;
        } else {
            valInput = \`
                <button type="button" onclick="event.stopPropagation(); saveJurnalPublic(\${item.id}, 'boolean', \${!isChecked})" class="flex-shrink-0 relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sc-teal-600 focus:ring-offset-2 \${isChecked ? 'bg-sc-teal-600' : 'bg-gray-200'}" role="switch" aria-checked="\${isChecked}">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out \${isChecked ? 'translate-x-5' : 'translate-x-0'}"></span>
                </button>
            \`;
        }

        let desc = item.deskripsi ? \`<p class="text-xs text-gray-500 mt-1 line-clamp-1">\${escHtml(item.deskripsi)}</p>\` : '';
        
        let subItems = '';
        if (item.nama === 'Membaca Alkitab Bersama-sama') {
            let plChecked = data.pl_checked ? 'checked' : '';
            let pbChecked = data.pb_checked ? 'checked' : '';
            
            subItems = \`
                <div class="mt-3 pl-2 border-l-2 border-gray-100 flex flex-col gap-2" onclick="event.stopPropagation();">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="pl_checkbox_\${item.id}" \${plChecked} onchange="saveJurnalPublic(\${item.id}, 'mab_pl', this.checked)" class="form-checkbox h-4 w-4 text-sc-teal-600 rounded border-gray-300 focus:ring-sc-teal-500">
                        <span class="ml-2 text-sm text-gray-700 font-medium">PL (Perjanjian Lama)</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="pb_checkbox_\${item.id}" \${pbChecked} onchange="saveJurnalPublic(\${item.id}, 'mab_pb', this.checked)" class="form-checkbox h-4 w-4 text-sc-teal-600 rounded border-gray-300 focus:ring-sc-teal-500">
                        <span class="ml-2 text-sm text-gray-700 font-medium">PB (Perjanjian Baru)</span>
                    </label>
                </div>
            \`;
        }

        card.innerHTML = \`
            <div class="flex-1 pr-4">
                <h5 class="text-sm font-bold text-gray-800 leading-tight">\${escHtml(item.nama)}</h5>
                \${desc}
                \${item.tipe === 'number' ? valInput : subItems}
            </div>
            \${item.tipe === 'boolean' ? valInput : ''}
        \`;

        if (item.tipe === 'boolean' && item.nama !== 'Membaca Alkitab Bersama-sama') {
            card.onclick = () => saveJurnalPublic(item.id, 'boolean', !isChecked);
        }

        container.appendChild(card);
    });
}

function saveJurnalPublic(itemId, actionType, val = null) {
    if (!currentUser) return;
    
    let payload = { user_id: currentUser.id, item_id: itemId, action: actionType };
    
    if (actionType === 'number') {
        let input = document.getElementById('val_' + itemId);
        if(!input || input.value === '') return;
        payload.value = input.value;
    } else if (actionType === 'boolean' || actionType === 'mab_pl' || actionType === 'mab_pb') {
        payload.value = val ? 1 : 0;
    }

    let Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });

    fetch(PUBLIC_SAVE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            playSound('success');
            openPublicJurnalModal(data);
        } else {
            playSound('error');
            Toast.fire({ icon: 'error', title: data.message || 'Gagal menyimpan.' });
        }
    })
    .catch(() => {
        playSound('error');
        Toast.fire({ icon: 'error', title: 'Terjadi kesalahan koneksi.' });
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
`;

const fs = require('fs');
fs.writeFileSync('scratch/final_js_restored.js', js);
console.log('JS successfully written!');
