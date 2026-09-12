<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::table('jurnal_life_checks')->updateOrInsert(
        ['student_id' => 99999, 'life_item_id' => 99999, 'tanggal' => '2026-09-08'],
        ['checked' => true, 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
    );
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
