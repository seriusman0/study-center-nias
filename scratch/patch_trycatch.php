<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

$oldFunc = <<<JS
function openPublicScanner() {
    document.getElementById('publicScannerModal').classList.remove('hidden');
    document.getElementById('scan-status').innerHTML = '<span class="text-blue-500">Meminta akses kamera...</span>';
    
    Html5Qrcode.getCameras().then(devices => {
JS;

$newFunc = <<<JS
function openPublicScanner() {
    try {
        document.getElementById('publicScannerModal').classList.remove('hidden');
        document.getElementById('scan-status').innerHTML = '<span class="text-blue-500">Meminta akses kamera...</span>';
        
        Html5Qrcode.getCameras().then(devices => {
JS;

$content = str_replace($oldFunc, $newFunc, $content);

$oldFuncEnd = <<<JS
    }).catch(err => {
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Izin kamera ditolak/gagal. Pastikan browser mengizinkan akses kamera. (' + err + ')</span>';
    });
}

document.getElementById('cameraSelect')
JS;

$newFuncEnd = <<<JS
    }).catch(err => {
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Izin kamera ditolak/gagal. Pastikan browser mengizinkan akses kamera. (' + err + ')</span>';
    });
    } catch(e) {
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Error sistem: ' + e.message + '</span>';
    }
}

document.getElementById('cameraSelect')
JS;

$content = str_replace($oldFuncEnd, $newFuncEnd, $content);

file_put_contents($file, $content);
echo "Patched try-catch\n";
