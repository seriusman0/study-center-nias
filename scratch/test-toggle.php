<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('username', 'yoziaepalmangulo')->first();
if (!$user) die("User not found\n");

$req = Illuminate\Http\Request::create('/jurnal-prajurit/toggle', 'POST', [
    'type' => 'life',
    'item_id' => 1,
    'date' => \Carbon\Carbon::now('Asia/Jakarta')->toDateString(),
    'checked' => true
]);
$req->setUserResolver(fn() => $user);

$controller = app(App\Http\Controllers\Web\Prajurit\PrajuritJurnalController::class);
try {
    $res = $controller->toggle($req);
    echo $res->getContent() . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
