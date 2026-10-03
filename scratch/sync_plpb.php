<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Sync PL (Item 9)
$checks9 = DB::table('jurnal_life_checks')->where('life_item_id', 9)->where('checked', 1)->get();
foreach ($checks9 as $c) {
    $date = date('Y-m-d', strtotime($c->tanggal));
    $exists = DB::table('jurnal_entries')
        ->where('student_id', $c->student_id)
        ->whereDate('tanggal', $date)
        ->first();
    
    if ($exists) {
        DB::table('jurnal_entries')->where('id', $exists->id)->update(['pl_checked' => 1]);
    } else {
        DB::table('jurnal_entries')->insert([
            'student_id' => $c->student_id,
            'tanggal' => $date,
            'pl_checked' => 1,
            'pb_checked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

// Sync PB (Item 10)
$checks10 = DB::table('jurnal_life_checks')->where('life_item_id', 10)->where('checked', 1)->get();
foreach ($checks10 as $c) {
    $date = date('Y-m-d', strtotime($c->tanggal));
    $exists = DB::table('jurnal_entries')
        ->where('student_id', $c->student_id)
        ->whereDate('tanggal', $date)
        ->first();
    
    if ($exists) {
        DB::table('jurnal_entries')->where('id', $exists->id)->update(['pb_checked' => 1]);
    } else {
        DB::table('jurnal_entries')->insert([
            'student_id' => $c->student_id,
            'tanggal' => $date,
            'pl_checked' => 0,
            'pb_checked' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

echo "Sync completed!\n";
