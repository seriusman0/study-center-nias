<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$andrian = \App\Models\User::where('name', 'LIKE', '%andrian pril%')->first();
if (!$andrian) { echo "Andrian not found.\n"; exit; }
echo "Andrian ID: {$andrian->id}, Cabang: {$andrian->cabang_id}\n";

$prajurit = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'prajurit');
})->where('id', '!=', $andrian->id)->whereHas('jurnalLifeChecks')->first();

if (!$prajurit) { echo "No prajurit found.\n"; exit; }

$targetEndDate = '2026-09-10';

// Let's get the distinct items from the prajurit
$templateItems = \App\Models\JurnalLifeCheck::where('student_id', $prajurit->id)
    ->select('life_item_id', 'checked', 'value')
    ->groupBy('life_item_id', 'checked', 'value')
    ->get();
    
$templateEntry = \App\Models\JurnalEntry::where('student_id', $prajurit->id)->orderBy('tanggal', 'desc')->first();

$currentDate = new DateTime('2026-09-01');
$endDateObj = new DateTime($targetEndDate);

$insertedEntries = 0;
$insertedLifeChecks = 0;

while ($currentDate <= $endDateObj) {
    $dateStr = $currentDate->format('Y-m-d');
    
    // Check if JurnalEntry exists
    $entry = \App\Models\JurnalEntry::firstOrNew([
        'student_id' => $andrian->id,
        'tanggal' => $dateStr
    ]);
    
    if (!$entry->exists) {
        $entry->cabang_id = $andrian->cabang_id;
        $entry->pl_checked = true;
        $entry->pb_checked = true;
        $entry->verse_week_key = $templateEntry ? $templateEntry->verse_week_key : null;
        $entry->verse_ref = $templateEntry ? $templateEntry->verse_ref : null;
        $entry->verse_checked = true;
        $entry->foto_belajar = $templateEntry ? $templateEntry->foto_belajar : null;
        $entry->save();
        $insertedEntries++;
    }
    
    // Check if LifeChecks exist
    foreach ($templateItems as $tItem) {
        $lc = \App\Models\JurnalLifeCheck::firstOrNew([
            'student_id' => $andrian->id,
            'tanggal' => $dateStr,
            'life_item_id' => $tItem->life_item_id
        ]);
        if (!$lc->exists) {
            $lc->checked = true; // assume checked
            $lc->value = $tItem->value;
            $lc->save();
            $insertedLifeChecks++;
        }
    }
    
    $currentDate->modify('+1 day');
}

echo "Done! Inserted $insertedEntries JurnalEntries and $insertedLifeChecks JurnalLifeChecks.\n";
