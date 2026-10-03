<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$items = DB::table('jurnal_life_items')
    ->whereIn('id', [2, 3, 9, 10, 23])
    ->get();

foreach ($items as $item) {
    echo "Item {$item->id}: {$item->label} | student_id: {$item->student_id} | kategori: {$item->kategori} \n";
}

$assignments = DB::table('jurnal_student_life_items')
    ->whereIn('life_item_id', [2, 3, 9, 10, 23])
    ->get();

echo "\nAssignments:\n";
foreach ($assignments as $a) {
    echo "Item {$a->life_item_id} -> Student {$a->student_id}\n";
}

// Check how many items Jan has access to vs User 74
$janItems = \App\Models\JurnalLifeItem::forStudent(127)->count();
$user74Items = \App\Models\JurnalLifeItem::forStudent(74)->count();
echo "\nJan total accessible items: $janItems\n";
echo "User 74 total accessible items: $user74Items\n";
