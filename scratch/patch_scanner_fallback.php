<?php

function patchFile($file) {
    $content = file_get_contents($file);
    
    // Find the initScanner function block
    $start = strpos($content, 'function initScanner(');
    if ($start === false) return;
    
    $end = strpos($content, '}', strpos($content, '}).catch(err => {', $start)) + 1;
    
    if ($end === false) return;

    $newInitScanner = <<<JS
function initScanner(cameraIdOrConfig, isFallback = false) {
    html5Qrcode = new Html5Qrcode("qr-reader");
    html5Qrcode.start(
        cameraIdOrConfig,
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess,
        (errorMessage) => { /* ignore */ }
    ).then(() => {
        let msg = document.getElementById('scan-status');
        if(msg) msg.innerHTML = 'Arahkan QR Code ke kamera.';
    }).catch(err => {
        if (!isFallback && err.toString().includes('OverconstrainedError')) {
            if (html5Qrcode) {
                html5Qrcode.clear();
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
JS;

    $content = substr_replace($content, $newInitScanner, $start, $end - $start);
    file_put_contents($file, $content);
    echo "Patched $file\n";
}

patchFile('resources/views/home.blade.php');
patchFile('resources/views/admin/prajurit-jurnal/dashboard.blade.php');

