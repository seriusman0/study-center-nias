<?php
$original = file_get_contents('scratch/rendered_home.html');
preg_match('/<script>(.*?)<\/script>/s', $original, $matches);
$js = $matches[1];

// Apply the safe startScanner
$oldStart = <<<JS
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
JS;

$newStart = <<<JS
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
JS;
$js = str_replace($oldStart, $newStart, $js);

// Apply try-catch for initScanner
$js = preg_replace("/if \(html5Qrcode\) \{\n\s*html5Qrcode\.clear\(\);\n\s*\}/s", "if (html5Qrcode) { try { html5Qrcode.clear(); } catch(e){} }", $js);

// Apply try-catch for openPublicScanner
$js = preg_replace('/function openPublicScanner\(\) \{/', "function openPublicScanner() {\n    try {", $js);
$js = preg_replace('/\}\)\.catch\(err => \{\n\s*document\.getElementById\(\'scan-status\'\)\.innerHTML = \'<span class="text-red-500">Izin kamera ditolak\/gagal.*?\}\);\n\}/s', "}).catch(err => { document.getElementById('scan-status').innerHTML = '<span class=\"text-red-500\">Izin kamera ditolak/gagal. Pastikan browser mengizinkan akses kamera. (' + err + ')</span>'; }); } catch(e) { document.getElementById('scan-status').innerHTML = '<span class=\"text-red-500\">Error sistem: ' + e.message + '</span>'; }\n}", $js);

// Remove aspectRatio: 1.0
$js = preg_replace('/\,\s*aspectRatio:\s*1\.0/', '', $js);

// Inject resetScannerUI
$resetJS = <<<JS
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
JS;
$js = str_replace('function closePublicScanner() {', $resetJS . "\n\nfunction closePublicScanner() {", $js);

// Fix closePublicScanner
$closeFix = <<<JS
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
    document.getElementById('scannerResultContainer').classList.add('hidden');
    document.getElementById('scannerCameraSection').classList.remove('hidden');
}
JS;
$js = preg_replace('/function closePublicScanner\(\) \{.*?\}\n\}/s', $closeFix, $js);

// Fix openPublicJurnalModal
$openModalFix = <<<JS
function openPublicJurnalModal(data) {
    document.getElementById('scannerCameraSection').classList.add('hidden');
    document.getElementById('scannerResultContainer').classList.remove('hidden');
JS;
$js = preg_replace('/function openPublicJurnalModal\(data\) \{\n\s*document\.getElementById\(\'scannerResultPlaceholder\'\).*?classList\.remove\(\'hidden\'\);/s', $openModalFix, $js);

// Remove placeholder logic from onScanSuccess if any? No, we don't have placeholder anymore, but we can leave the remaining as is.

// Now replace the <script> block in home.blade.php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);
$content = preg_replace('/<script>.*?<\/script>/s', "<script>\n" . $js . "\n</script>", $content);

file_put_contents($file, $content);
echo "Restored and safely patched JS in home.blade.php\n";
