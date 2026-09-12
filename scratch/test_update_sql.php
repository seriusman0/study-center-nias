<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$model = new \App\Models\JurnalLifeCheck();
$model->exists = true;
$model->student_id = 1;
$model->life_item_id = 1;
$model->tanggal = '2026-09-01';
$model->value = 5;

// Mock the connection to just dump the query
DB::listen(function($query) {
    echo $query->sql . "\n";
    print_r($query->bindings);
});

try {
    $model->update(['value' => 17]);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
