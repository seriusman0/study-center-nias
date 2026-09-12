<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$model = new \App\Models\JurnalLifeCheck([
    'student_id' => 1, 'life_item_id' => 1, 'tanggal' => '2026-09-01', 'value' => 5
]);
$model->exists = true;
$model->syncOriginal(); // make them not dirty

DB::listen(function($query) {
    echo $query->sql . "\n";
});

try {
    $model->fill(['value' => 17])->save();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
