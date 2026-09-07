<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$json = file_get_contents(__DIR__.'/pembacaan-alkitab-2-pasal-per-hari.json');
$data = json_decode($json, true);

$schedule = App\Models\CollegeBibleSchedule::create([
    'name' => 'Pembacaan Alkitab 2 Pasal Per Hari',
    'description' => 'Jadwal pembacaan alkitab 2 pasal per hari dari file JSON',
]);

foreach ($data as $row) {
    App\Models\CollegeBibleItem::create([
        'schedule_id' => $schedule->id,
        'day_no' => $row['no'],
        'pl_text' => $row['PL'],
        'pb_text' => $row['PB'],
    ]);
}

$config = App\Models\CollegeConfig::current();
$config->active_schedule_id = $schedule->id;
$config->anchor_day_no = 1;
$config->anchor_date = '2026-09-06';
$config->save();

echo "Schedule imported successfully with ID: {$schedule->id}\n";
