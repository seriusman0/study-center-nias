<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::capture());
$config = App\Models\CollegeConfig::current();
echo "Form Open: " . $config->form_open_time . "\n";
echo "Form Close: " . $config->form_close_time . "\n";
echo "Current time: " . \Carbon\Carbon::now(App\Support\JurnalWeek::TZ)->format('H:i:s') . "\n";
echo "Is Form Open? " . ($config->isFormOpen() ? 'Yes' : 'No') . "\n";
