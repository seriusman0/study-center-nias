<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

$btn = <<<HTML
        <div class="mt-8 flex justify-center w-full relative z-10">
            <button type="button" onclick="openPublicScanner()" class="px-8 py-4 bg-white text-sc-teal-700 font-bold rounded-xl shadow-lg hover:bg-gray-50 transition transform hover:-translate-y-1 inline-flex items-center gap-2 text-lg border-b-4 border-sc-teal-200">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><rect x="7" y="7" width="3" height="3"/><rect x="14" y="7" width="3" height="3"/><rect x="7" y="14" width="3" height="3"/><rect x="14" y="14" width="3" height="3"/></svg>
                Isi Jurnal via Scan QR
            </button>
        </div>
HTML;

if (strpos($content, 'Isi Jurnal via Scan QR') === false) {
    $content = preg_replace('/<\/div>\s*<\/section>/', "    </div>\n" . $btn . "\n</section>", $content, 1);
}

$modalAndScript = <<<HTML

{{-- Public Scanner Modal --}}
<div id="publicScannerModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-4xl w-full border border-gray-100">
            <div class="bg-sc-teal-600 px-4 py-4 flex justify-between items-center text-white">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><rect x="7" y="7" width="3" height="3"/><rect x="14" y="7" width="3" height="3"/><rect x="7" y="14" width="3" height="3"/><rect x="14" y="14" width="3" height="3"/></svg>
                    Scanner & Jurnal
                </h3>
                <button type="button" onclick="closePublicScanner()" class="text-white hover:text-gray-200 focus:outline-none">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                    {{-- Left Column: Camera --}}
                    <div class="md:col-span-5 flex flex-col">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kamera</label>
                            <select id="cameraSelect" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sc-teal-500 focus:ring-sc-teal-500 py-2 px-3 text-sm border bg-white">
                                <option value="environment">Kamera Belakang</option>
                                <option value="user">Kamera Depan</option>
                            </select>
                        </div>
                        <div id="qr-reader" class="w-full rounded-xl overflow-hidden shadow-md border-4 border-gray-100 bg-gray-50 flex-grow" style="min-height: 250px;"></div>
                        <div id="scan-status" class="mt-4 text-center text-gray-500 font-medium font-mono text-sm px-4 py-2 bg-gray-100 rounded-lg">
                            Arahkan QR Code ke kamera.
                        </div>
                    </div>
                    
                    {{-- Right Column: Result --}}
                    <div class="md:col-span-7 bg-gray-50 rounded-xl p-5 border border-gray-200" id="scannerResultContainer">
                        <div id="scannerResultPlaceholder" class="flex flex-col items-center justify-center h-full min-h-[300px] text-gray-400">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mb-4"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><rect x="7" y="7" width="3" height="3"/><rect x="14" y="7" width="3" height="3"/><rect x="7" y="14" width="3" height="3"/><rect x="14" y="14" width="3" height="3"/></svg>
                            <h5 class="text-xl font-bold text-gray-500 mb-2">Menunggu Hasil Scan...</h5>
                            <p class="text-sm text-center">Scan QR Anda untuk mulai mengisi jurnal.</p>
                        </div>
                        
                        <div id="scannerResultContent" class="hidden">
                            <div class="flex items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-5">
                                <div id="jurnalAvatarCol" class="flex-shrink-0"></div>
                                <div>
                                    <h4 class="font-bold text-lg text-gray-800" id="jurnalModalTitleName"></h4>
                                    <div id="jurnalPrajuritInfo" class="text-sm text-gray-500"></div>
                                </div>
                            </div>
                            
                            <h5 class="font-bold text-sc-ink-900 mb-3 px-1 border-b border-gray-200 pb-2">Checklist Jurnal Hari Ini</h5>
                            <form id="publicJurnalForm" onsubmit="return false;">
                                <div id="jurnalChecklistContainer" class="space-y-3 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3 rounded-b-2xl border-t border-gray-200">
                <button type="button" onclick="closePublicScanner()" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition shadow-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const PUBLIC_SCAN_URL  = "{{ route('public-jurnal.scan') }}";
const PUBLIC_SAVE_URL  = "{{ route('public-jurnal.save') }}";
const CSRF      = "{{ csrf_token() }}";

let html5Qrcode = null;
let scanning    = false;
let currentUser = null;
let currentItems = [];
let currentDate = null;

function playSound(type) {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        if (type === 'success') {
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(800, audioCtx.currentTime);
            oscillator.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
            gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.2);
            oscillator.start(audioCtx.currentTime);
            oscillator.stop(audioCtx.currentTime + 0.2);
        } else {
            oscillator.type = 'sawtooth';
            oscillator.frequency.setValueAtTime(300, audioCtx.currentTime);
            oscillator.frequency.exponentialRampToValueAtTime(150, audioCtx.currentTime + 0.2);
            gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
            oscillator.start(audioCtx.currentTime);
            oscillator.stop(audioCtx.currentTime + 0.3);
        }
    } catch(e) {}
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function startScanner(cameraIdOrConfig) {
    if (html5Qrcode) {
        html5Qrcode.stop().then(() => {
            html5Qrcode.clear();
            initScanner(cameraIdOrConfig);
        }).catch(() => {
            initScanner(cameraIdOrConfig);
        });
    } else {
        initScanner(cameraIdOrConfig);
    }
}

function initScanner(cameraIdOrConfig) {
    html5Qrcode = new Html5Qrcode("qr-reader");
    html5Qrcode.start(
        cameraIdOrConfig,
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess,
        (errorMessage) => { /* ignore */ }
    ).then(() => {
        document.getElementById('scan-status').innerHTML = 'Arahkan QR Code ke kamera.';
    }).catch(err => {
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Gagal memulai kamera: ' + err + '</span>';
    });
}

function openPublicScanner() {
    document.getElementById('publicScannerModal').classList.remove('hidden');
    document.getElementById('scan-status').innerHTML = '<span class="text-blue-500">Meminta akses kamera...</span>';
    
    Html5Qrcode.getCameras().then(devices => {
        let select = document.getElementById('cameraSelect');
        select.innerHTML = '';
        if (devices && devices.length > 0) {
            devices.forEach(cam => {
                let opt = document.createElement('option');
                opt.value = cam.id;
                opt.text = cam.label || 'Kamera ' + cam.id;
                select.appendChild(opt);
            });
            let frontOpt = document.createElement('option');
            frontOpt.value = 'user';
            frontOpt.text = 'Kamera Depan (Default)';
            select.insertBefore(frontOpt, select.firstChild);
            
            let backOpt = document.createElement('option');
            backOpt.value = 'environment';
            backOpt.text = 'Kamera Belakang (Default)';
            select.insertBefore(backOpt, select.firstChild);
            
            select.value = 'environment';
        } else {
            select.innerHTML = '<option value="environment">Kamera Belakang</option><option value="user">Kamera Depan</option>';
        }

        setTimeout(() => {
            if (document.getElementById('publicScannerModal').classList.contains('hidden')) return;
            startScanner('environment');
        }, 400); 
    }).catch(err => {
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Izin kamera ditolak/gagal. Pastikan browser mengizinkan akses kamera. (' + err + ')</span>';
    });
}

document.getElementById('cameraSelect').addEventListener('change', function() {
    let val = this.value;
    if (val === 'environment' || val === 'user') {
        startScanner({ facingMode: val });
    } else {
        startScanner(val);
    }
});

function closePublicScanner() {
    document.getElementById('publicScannerModal').classList.add('hidden');
    if (html5Qrcode) {
        try {
            html5Qrcode.stop().then(() => {
                html5Qrcode.clear();
                html5Qrcode = null;
            }).catch(() => { html5Qrcode = null; });
        } catch(e) {}
    }
    document.getElementById('scannerResultContent').classList.add('hidden');
    document.getElementById('scannerResultPlaceholder').classList.remove('hidden');
    document.getElementById('scannerResultPlaceholder').classList.add('flex');
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
            document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Tidak ada item jurnal untuk pengguna ini.</span>';
            setTimeout(() => { scanning = false; }, 2500);
            return;
        }

        playSound('success');
        currentUser = data.user;
        currentItems = data.items;
        currentDate = data.today;
        
        document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">Berhasil! Lanjut scan QR lain jika perlu.</span>';
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
    document.getElementById('scannerResultPlaceholder').classList.remove('flex');
    document.getElementById('scannerResultPlaceholder').classList.add('hidden');
    
    const contentDiv = document.getElementById('scannerResultContent');
    contentDiv.classList.remove('hidden');

    document.getElementById('jurnalModalTitleName').innerHTML = escHtml(data.user.name);
    let avatarUrl = data.user.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.user.name)+'&size=150&background=0F766E&color=fff';
    
    document.getElementById('jurnalAvatarCol').innerHTML = `<img src="\${avatarUrl}" class="rounded-full shadow-sm object-cover border-4 border-sc-teal-200" style="width: 70px; height: 70px;" alt="Foto Profil">`;

    let kelasHtml = data.user.kelas ? `Kelas/Info: <strong>\${escHtml(data.user.kelas)}</strong> <span class="mx-2 text-gray-300">|</span>` : ``;
    document.getElementById('jurnalPrajuritInfo').innerHTML = `\${kelasHtml} Tanggal: <strong>\${data.today_formatted || data.today}</strong>`;

    const container = document.getElementById('jurnalChecklistContainer');
    container.innerHTML = '';

    let checkedIds = data.checkedIds || [];
    let numberValues = data.numberValues || {};

    data.items.forEach(item => {
        let isChecked = checkedIds.includes(item.id);
        
        let card = document.createElement('div');
        card.className = "flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl hover:shadow-sm transition cursor-pointer group";
        
        let valInput = '';
        if (item.response_type === 'number') {
            let val = numberValues[item.id] || 0;
            valInput = `
                <div class="ml-3 flex items-center gap-2" onclick="event.stopPropagation()">
                    <input type="number" min="0" class="jurnal-val-input border border-gray-300 rounded px-2 py-1 w-20 text-center text-sm" data-id="\${item.id}" value="\${val}" onchange="autoSavePublicJurnal()">
                </div>
            `;
        }

        card.innerHTML = `
            <div class="flex items-center gap-3 flex-grow" onclick="togglePublicCheck(\${item.id})">
                <div class="relative flex items-center justify-center w-6 h-6 rounded-md border-2 transition-colors \${isChecked ? 'bg-sc-teal-500 border-sc-teal-500' : 'bg-white border-gray-300 group-hover:border-sc-teal-400'}" id="chkbox-bg-\${item.id}">
                    <svg class="\${isChecked ? 'opacity-100' : 'opacity-0'} text-white transition-opacity w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-800">\${escHtml(item.label)}</span>
                    <span class="text-xs px-2 py-0.5 ml-2 bg-gray-100 text-gray-600 rounded-full">\${escHtml(item.kategori)}</span>
                </div>
                <input type="checkbox" class="jurnal-item-check hidden" data-id="\${item.id}" \${isChecked ? 'checked' : ''}>
            </div>
            \${valInput}
        `;
        container.appendChild(card);
    });
}

function togglePublicCheck(id) {
    let cb = document.querySelector('.jurnal-item-check[data-id="'+id+'"]');
    if (!cb) return;
    cb.checked = !cb.checked;
    
    let bg = document.getElementById('chkbox-bg-'+id);
    let svg = bg.querySelector('svg');
    
    if (cb.checked) {
        bg.classList.remove('bg-white', 'border-gray-300');
        bg.classList.add('bg-sc-teal-500', 'border-sc-teal-500');
        svg.classList.remove('opacity-0');
        svg.classList.add('opacity-100');
    } else {
        bg.classList.remove('bg-sc-teal-500', 'border-sc-teal-500');
        bg.classList.add('bg-white', 'border-gray-300');
        svg.classList.remove('opacity-100');
        svg.classList.add('opacity-0');
    }
    
    autoSavePublicJurnal();
}

function autoSavePublicJurnal() {
    if (!currentUser || !currentDate) return;
    
    let checks = [];
    document.querySelectorAll('.jurnal-item-check').forEach(cb => {
        let id = parseInt(cb.getAttribute('data-id'));
        let valInput = document.querySelector('.jurnal-val-input[data-id="'+id+'"]');
        let val = valInput ? parseFloat(valInput.value) || 0 : null;
        
        checks.push({
            item_id: id,
            checked: cb.checked,
            value: val
        });
    });

    fetch(PUBLIC_SAVE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ user_id: currentUser.id, tanggal: currentDate, checks: checks })
    }).catch(()=>{});
}
</script>
<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
</style>
@endpush
HTML;

if (strpos($content, 'publicScannerModal') === false) {
    $content = str_replace('@endsection', $modalAndScript . "\n@endsection", $content);
    file_put_contents($file, $content);
    echo "Patched home view with modal.";
} else {
    echo "Modal already exists.";
}

