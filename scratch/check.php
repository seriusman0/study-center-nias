<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$config = App\Models\CollegeConfig::current();
echo "Open: " . $config->form_open_time . "\n";
echo "Close: " . $config->form_close_time . "\n";
$now = \Carbon\Carbon::now('Asia/Jakarta');
echo "Now: " . $now->toTimeString() . "\n";
echo "isFormOpen: " . ($config->isFormOpen() ? 'Yes' : 'No') . "\n";
