import re

with open('scratch/extracted_script.js', 'r') as f:
    js = f.read()

# Replace variables
js = js.replace('const PUBLIC_SCAN_URL  = "http://localhost/public-jurnal/scan";', 'const PUBLIC_SCAN_URL  = "{{ route(\'public-jurnal.scan\') }}";')
js = js.replace('const PUBLIC_SAVE_URL  = "http://localhost/public-jurnal/save";', 'const PUBLIC_SAVE_URL  = "{{ route(\'public-jurnal.save\') }}";')
js = re.sub(r'const CSRF\s*=\s*".*?";', 'const CSRF = "{{ csrf_token() }}";', js)

# Remove aspectRatio
js = re.sub(r',\s*aspectRatio:\s*1\.0', '', js)

# Fix openPublicJurnalModal toggle
js = js.replace("document.getElementById('scannerResultPlaceholder').classList.remove('flex');\n    document.getElementById('scannerResultPlaceholder').classList.add('hidden');", "document.getElementById('scannerCameraSection').classList.add('hidden');\n    document.getElementById('scannerResultContainer').classList.remove('hidden');")

# Fix initScanner try-catch for clear()
js = js.replace("if (html5Qrcode) {\n                html5Qrcode.clear();\n            }", "if (html5Qrcode) { try { html5Qrcode.clear(); } catch(e){} }")

# Safe startScanner
old_start = """function startScanner(cameraIdOrConfig) {
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
}"""
new_start = """function startScanner(cameraIdOrConfig) {
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
}"""
js = js.replace(old_start, new_start)

# Add resetScannerUI and update closePublicScanner
reset_js = """function resetScannerUI() {
    document.getElementById('scannerResultContainer').classList.add('hidden');
    document.getElementById('scannerCameraSection').classList.remove('hidden');
    document.getElementById('scan-status').innerHTML = '<span class="text-gray-600">Arahkan QR Code ke kamera.</span>';
    if(html5Qrcode && html5Qrcode.getState() !== 2) {
        let select = document.getElementById('cameraSelect');
        if(select && select.value) {
            startScanner(select.value);
        }
    }
}"""
js = js.replace("function closePublicScanner() {", reset_js + "\n\nfunction closePublicScanner() {")

old_close = """function closePublicScanner() {
    document.getElementById('publicScannerModal').classList.add('hidden');
    if (html5Qrcode) {
        html5Qrcode.stop().then(() => {
            html5Qrcode.clear();
            html5Qrcode = null;
        }).catch(() => {
            html5Qrcode = null;
        });
    }
    document.getElementById('scannerResultContent').classList.add('hidden');
    document.getElementById('scannerResultPlaceholder').classList.remove('hidden');
    document.getElementById('scannerResultPlaceholder').classList.add('flex');
}"""
new_close = """function closePublicScanner() {
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
}"""
js = js.replace(old_close, new_close)

# openPublicScanner Try-catch
old_open = """function openPublicScanner() {
    document.getElementById('publicScannerModal').classList.remove('hidden');
    document.getElementById('scan-status').innerHTML = '<span class="text-blue-500">Meminta akses kamera...</span>';
    
    Html5Qrcode.getCameras().then(devices => {"""
new_open = """function openPublicScanner() {
    try {
        document.getElementById('publicScannerModal').classList.remove('hidden');
        document.getElementById('scan-status').innerHTML = '<span class="text-blue-500">Meminta akses kamera...</span>';
        
        Html5Qrcode.getCameras().then(devices => {"""
js = js.replace(old_open, new_open)

old_catch = """    }).catch(err => {
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Izin kamera ditolak/gagal. Pastikan browser mengizinkan akses kamera. (' + err + ')</span>';
    });
}"""
new_catch = """    }).catch(err => {
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Izin kamera ditolak/gagal. Pastikan browser mengizinkan akses kamera. (' + err + ')</span>';
    });
    } catch (e) {
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Error sistem: ' + e.message + '</span>';
    }
}"""
js = js.replace(old_catch, new_catch)

with open('scratch/final_js_restored.js', 'w') as f:
    f.write(js)
print('Done in python')
