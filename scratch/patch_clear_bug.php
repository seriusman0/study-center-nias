<?php

function patchFile($file) {
    $content = file_get_contents($file);

    $oldCode = <<<JS
        if (!isFallback && err.toString().includes('OverconstrainedError')) {
            if (html5Qrcode) {
                html5Qrcode.clear();
            }
            let select = document.getElementById('cameraSelect');
JS;

    $newCode = <<<JS
        if (!isFallback && err.toString().includes('OverconstrainedError')) {
            if (html5Qrcode) {
                try { html5Qrcode.clear(); } catch(e) {}
            }
            let select = document.getElementById('cameraSelect');
JS;

    if (strpos($content, $oldCode) !== false) {
        $content = str_replace($oldCode, $newCode, $content);
        file_put_contents($file, $content);
        echo "Patched \$file\n";
    }
}

patchFile('resources/views/home.blade.php');
patchFile('resources/views/admin/prajurit-jurnal/dashboard.blade.php');

