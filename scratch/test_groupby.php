<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$checks = collect([
    new \App\Models\JurnalLifeCheck(['life_item_id' => 1, 'tanggal' => '2026-09-01', 'value' => 10, 'checked' => true]),
    new \App\Models\JurnalLifeCheck(['life_item_id' => 1, 'tanggal' => '2026-09-02', 'value' => 17, 'checked' => true]),
]);

$grouped = $checks->groupBy(fn($c) => $c->tanggal->toDateString());
foreach ($grouped as $key => $dayChecks) {
    echo $key . " -> " . $dayChecks->firstWhere('life_item_id', 1)->value . "\n";
}
