<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

$oldJs = <<<JS
        if (data.status === 'redirect') {
            playSound('success');
            document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">Berhasil! Mengalihkan...</span>';
            window.location.href = data.url;
            return;
        }
JS;

$newJs = <<<JS
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
JS;

$content = str_replace($oldJs, $newJs, $content);
file_put_contents($file, $content);
echo "Patched mobile redirect logic.\n";
