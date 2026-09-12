<?php
$file = 'resources/views/admin/prajurit-jurnal/dashboard.blade.php';
$content = file_get_contents($file);

$oldConfig = '{ fps: 10, qrbox: { width: 250, height: 250 } }';
$newConfig = <<<JS
{
            fps: 10,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                let minEdgePercentage = 0.7;
                let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                return { width: qrboxSize, height: qrboxSize };
            },
            aspectRatio: 1.0
        }
JS;

$content = str_replace($oldConfig, $newConfig, $content);
file_put_contents($file, $content);
echo "Patched qr-reader UI and config in dashboard.blade.php";
