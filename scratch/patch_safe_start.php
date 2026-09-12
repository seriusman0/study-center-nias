<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

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
JS;

if (strpos($content, $oldStart) !== false) {
    $content = str_replace($oldStart, $newStart, $content);
    file_put_contents($file, $content);
    echo "Patched safe startScanner\n";
}

// Also for dashboard.blade.php
$file2 = 'resources/views/admin/prajurit-jurnal/dashboard.blade.php';
$content2 = file_get_contents($file2);
if (strpos($content2, $oldStart) !== false) {
    $content2 = str_replace($oldStart, $newStart, $content2);
    file_put_contents($file2, $content2);
    echo "Patched safe startScanner in dashboard\n";
}
