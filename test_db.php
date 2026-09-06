<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
config(['database.connections.mysql.host' => '127.0.0.1']);
echo "Total Presensi: " . \App\Models\Presensi::count() . "\n";
echo "Total Presensi Students: " . \Illuminate\Support\Facades\DB::table('presensi_students')->count() . "\n";
