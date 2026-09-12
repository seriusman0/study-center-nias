<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$andrian = \App\Models\User::where('name', 'LIKE', '%andrian pril%')->first();
$entries = \App\Models\JurnalEntry::where('student_id', $andrian->id)->orderBy('tanggal')->get();
echo "JurnalEntries for Andrian:\n";
foreach($entries as $e) {
    echo $e->tanggal->format('Y-m-d') . " - PL: " . $e->pl_checked . " PB: " . $e->pb_checked . "\n";
}

$lcs = \App\Models\JurnalLifeCheck::where('student_id', $andrian->id)->count();
echo "Total LifeChecks: $lcs\n";
