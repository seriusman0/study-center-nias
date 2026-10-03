<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Support\JurnalWeek;

// Sync Baca Alkitab (Item 2) to PL and PB
$checks2 = DB::table('jurnal_life_checks')->where('life_item_id', 2)->where('checked', 1)->get();
foreach ($checks2 as $c) {
    $date = date('Y-m-d', strtotime($c->tanggal));
    $exists = DB::table('jurnal_entries')
        ->where('student_id', $c->student_id)
        ->whereDate('tanggal', $date)
        ->first();
    
    if ($exists) {
        DB::table('jurnal_entries')->where('id', $exists->id)->update([
            'pl_checked' => 1,
            'pb_checked' => 1
        ]);
    } else {
        DB::table('jurnal_entries')->insert([
            'student_id' => $c->student_id,
            'tanggal' => $date,
            'pl_checked' => 1,
            'pb_checked' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

// Sync Hafal Ayat (Item 3) to verse_checked and verse_week_key
$checks3 = DB::table('jurnal_life_checks')->where('life_item_id', 3)->where('checked', 1)->get();
foreach ($checks3 as $c) {
    $date = \Carbon\Carbon::parse($c->tanggal);
    $dateStr = $date->toDateString();
    $weekKey = JurnalWeek::weekKeyFor($date);
    
    $exists = DB::table('jurnal_entries')
        ->where('student_id', $c->student_id)
        ->whereDate('tanggal', $dateStr)
        ->first();
    
    if ($exists) {
        DB::table('jurnal_entries')->where('id', $exists->id)->update([
            'verse_checked' => 1,
            'verse_week_key' => $weekKey
        ]);
    } else {
        DB::table('jurnal_entries')->insert([
            'student_id' => $c->student_id,
            'tanggal' => $dateStr,
            'verse_checked' => 1,
            'verse_week_key' => $weekKey,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

echo "Sync completed for item 2 and 3!\n";
