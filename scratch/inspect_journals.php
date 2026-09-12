<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$andrian = \App\Models\User::where('name', 'LIKE', '%andrian pril%')->first();
if (!$andrian) { echo "Andrian not found.\n"; exit; }

$prajurit = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'prajurit');
})->where('id', '!=', $andrian->id)->whereHas('jurnalLifeChecks')->first();

if (!$prajurit) { echo "No other prajurit with journals found.\n"; exit; }

echo "Found Prajurit: {$prajurit->name} (ID: {$prajurit->id})\n";
$latestEntry = \App\Models\JurnalEntry::where('student_id', $prajurit->id)->orderBy('tanggal', 'desc')->first();
echo "Latest JurnalEntry for prajurit: " . ($latestEntry ? $latestEntry->tanggal : 'None') . "\n";

$latestLifeCheck = \App\Models\JurnalLifeCheck::where('student_id', $prajurit->id)->orderBy('tanggal', 'desc')->first();
echo "Latest JurnalLifeCheck for prajurit: " . ($latestLifeCheck ? $latestLifeCheck->tanggal : 'None') . "\n";

