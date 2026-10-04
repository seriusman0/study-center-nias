<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teenager = \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'scholarship_teenager'); })->first();

auth()->login($teenager);
$request = Illuminate\Http\Request::create('/jurnal-scholarship-teenager', 'GET');
$response = app()->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Error: " . $response->exception?->getMessage() . "\n";
