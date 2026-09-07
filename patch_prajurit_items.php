<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$item = App\Models\JurnalLifeItem::where('kategori', 'prajurit')->where('label', 'Jumlah Salah Ayat Hafalan')->first();
if ($item) {
    $item->label = 'Jumlah Benar ayat Hafalan';
    $item->save();
    echo "Updated label.\n";
} else {
    echo "Item not found.\n";
}

$newItem = App\Models\JurnalLifeItem::firstOrCreate([
    'kategori' => 'prajurit',
    'label' => 'Membaca Alkitab Bersama-sama',
], [
    'response_type' => 'boolean',
    'reset_period' => 'daily',
    'is_default' => true,
    'is_active' => true,
]);
echo "Created/ensured 'Membaca Alkitab Bersama-sama'.\n";
