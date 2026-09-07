<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$config = App\Models\CollegeConfig::current();
$config->active_schedule_id = 1; // back to Jadwal 1
$config->anchor_day_no = 187;
$config->anchor_date = '2026-07-06';
$config->save();
