<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'LIKE', '%andrian pril%')->first();
if (!$user) { echo "User Andrian not found\n"; exit; }
echo "Andrian ID: {$user->id}\n";

$other = \App\Models\User::where('role', 'prajurit')->where('id', '!=', $user->id)->first();
if (!$other) { echo "No other prajurit\n"; exit; }
echo "Other Prajurit: {$other->name} (ID: {$other->id})\n";

$start_date = '2026-08-01'; // just as an example
$end_date = '2026-09-10';

// Check JurnalEntry for other
$entries = \App\Models\JurnalEntry::where('student_id', $other->id)
    ->whereBetween('tanggal', [$start_date, $end_date])
    ->get();
echo "Found {$entries->count()} JurnalEntry for {$other->name}\n";

// Check JurnalLifeCheck for other
$lifeChecks = \App\Models\JurnalLifeCheck::where('student_id', $other->id)
    ->whereBetween('tanggal', [$start_date, $end_date])
    ->get();
echo "Found {$lifeChecks->count()} JurnalLifeCheck for {$other->name}\n";
