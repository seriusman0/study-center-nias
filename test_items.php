<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'prajurit'))->first();
if($user) {
    $c = new App\Http\Controllers\Web\Admin\PrajuritJurnalAdminController();
    $matrix = $c->summary(request(), $user)->getData(true)['matrix'];
    echo "Headers: " . count($matrix['headers']) . "\n";
    echo "Items: " . count($matrix['items']) . "\n";
}
