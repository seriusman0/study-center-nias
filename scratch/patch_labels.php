<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);
$oldLabels = <<<JS
    const categoryLabels = {
        'pembacaan': 'Pembacaan Firman',
        'sidang': 'Sidang Gereja',
        'rohani': 'Kegiatan Rohani',
        'prajurit': 'Prajurit',
        'lain-lain': 'Lain-lain'
    };
JS;
$newLabels = <<<JS
    const categoryLabels = {
        'kerohanian': 'Kerohanian',
        'pendidikan': 'Pendidikan',
        'karakter'  : 'Karakter',
        'pembacaan' : 'Pembacaan',
        'sidang'    : 'Sidang',
        'rohani'    : 'Rohani',
        'prajurit'  : 'Prajurit',
        'lain-lain' : 'Lain-lain'
    };
JS;
$content = str_replace($oldLabels, $newLabels, $content);
file_put_contents($file, $content);
echo "Patched labels.\n";
