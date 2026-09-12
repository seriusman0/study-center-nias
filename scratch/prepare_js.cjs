const fs = require('fs');
let js = fs.readFileSync('scratch/extracted_script.js', 'utf8');

// Restore blade directives
js = js.replace(/const PUBLIC_SCAN_URL\s*=\s*".*?";/, 'const PUBLIC_SCAN_URL  = "{{ route(\'public-jurnal.scan\') }}";');
js = js.replace(/const PUBLIC_SAVE_URL\s*=\s*".*?";/, 'const PUBLIC_SAVE_URL  = "{{ route(\'public-jurnal.save\') }}";');
js = js.replace(/const CSRF\s*=\s*".*?";/, 'const CSRF      = "{{ csrf_token() }}";');

// Now, fix the initScanner crash bug
js = js.replace(/if \(html5Qrcode\) \{\n\s*html5Qrcode\.clear\(\);\n\s*\}/s, "if (html5Qrcode) { try { html5Qrcode.clear(); } catch(e){} }");

// Safe startScanner
const oldStart = /function startScanner\(cameraIdOrConfig\).*?else \{\n\s*initScanner\(cameraIdOrConfig\);\n\s*\}\n\}/s;
const newStart = `function startScanner(cameraIdOrConfig) {
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
}`;
js = js.replace(oldStart, newStart);

// Try-catch for openPublicScanner
js = js.replace(/function openPublicScanner\(\) \{/, "function openPublicScanner() {\n    try {");
js = js.replace(/\}\)\.catch\(err => \{\n\s*document\.getElementById\('scan-status'\)\.innerHTML.*?\}\);\n\}/s, `}).catch(err => { document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Izin kamera ditolak/gagal. Pastikan browser mengizinkan akses kamera. (' + err + ')</span>'; }); } catch(e) { document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Error sistem: ' + e.message + '</span>'; }\n}`);

// Remove aspectRatio: 1.0
js = js.replace(/,\s*aspectRatio:\s*1\.0/g, '');

// Fix resetScannerUI
const resetJS = `function resetScannerUI() {
    document.getElementById('scannerResultContainer').classList.add('hidden');
    document.getElementById('scannerCameraSection').classList.remove('hidden');
    document.getElementById('scan-status').innerHTML = '<span class="text-gray-600">Arahkan QR Code ke kamera.</span>';
    if(html5Qrcode && html5Qrcode.getState() !== 2) {
        let select = document.getElementById('cameraSelect');
        if(select && select.value) {
            startScanner(select.value);
        }
    }
}`;
js = js.replace('function closePublicScanner() {', resetJS + "\n\nfunction closePublicScanner() {");

// Hide result / show camera in close
const closeFix = `function closePublicScanner() {
    document.getElementById('publicScannerModal').classList.add('hidden');
    if (html5Qrcode) {
        try {
            html5Qrcode.stop().then(() => {
                html5Qrcode.clear();
                html5Qrcode = null;
            }).catch(() => { html5Qrcode = null; });
        } catch(e) {}
    }
    document.getElementById('scannerResultContainer').classList.add('hidden');
    document.getElementById('scannerCameraSection').classList.remove('hidden');
}`;
js = js.replace(/function closePublicScanner\(\) \{.*?\}\n\}/s, closeFix);

// In openPublicJurnalModal, toggle views
const openModalFix = `function openPublicJurnalModal(data) {
    document.getElementById('scannerCameraSection').classList.add('hidden');
    document.getElementById('scannerResultContainer').classList.remove('hidden');`;
js = js.replace(/function openPublicJurnalModal\(data\) \{\n\s*document\.getElementById\('scannerResultPlaceholder'\).*?classList\.remove\('hidden'\);/s, openModalFix);

fs.writeFileSync('scratch/final_js_restored.js', js);
console.log('Processed JS successfully');
