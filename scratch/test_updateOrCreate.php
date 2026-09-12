<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = DB::connection();
// Start transaction so we don't mess up data
$db->beginTransaction();

try {
    // try to updateOrCreate a check
    $check = \App\Models\JurnalLifeCheck::updateOrCreate(
        ['student_id' => 1, 'life_item_id' => 1, 'tanggal' => '2026-09-01'],
        ['checked' => true, 'value' => 5]
    );
    echo "First call:\n";
    print_r($check->toArray());

    $check2 = \App\Models\JurnalLifeCheck::updateOrCreate(
        ['student_id' => 1, 'life_item_id' => 1, 'tanggal' => '2026-09-02'],
        ['checked' => true, 'value' => 17]
    );
    
    // Now let's try to update the first one
    $check3 = \App\Models\JurnalLifeCheck::updateOrCreate(
        ['student_id' => 1, 'life_item_id' => 1, 'tanggal' => '2026-09-01'],
        ['checked' => true, 'value' => 10]
    );
    
    echo "Third call (updating first):\n";
    print_r($check3->toArray());

    $all = \App\Models\JurnalLifeCheck::where('student_id', 1)->where('life_item_id', 1)->get();
    echo "All in DB:\n";
    foreach($all as $c) {
        echo $c->tanggal->toDateString() . " => " . $c->value . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
$db->rollBack();
