<?php
$file = 'resources/views/admin/prajurit-jurnal/dashboard.blade.php';
$content = file_get_contents($file);

// Reduce camera size on mobile by adding inline style max-width to qr-reader
$content = str_replace(
    'id="qr-reader" style="width:100%; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);"',
    'id="qr-reader" style="width:100%; max-width: 320px; margin: 0 auto; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);"',
    $content
);

// Reduce min-height of placeholder
$content = str_replace('min-height: 400px;', 'min-height: 250px;', $content);

// Reduce padding in columns
$content = str_replace('col-md-5 bg-dark p-4', 'col-md-5 bg-dark p-3', $content);
$content = str_replace('col-md-7 p-4 bg-light', 'col-md-7 p-3 bg-light', $content);

file_put_contents($file, $content);
echo "Patched admin modal size.\n";
