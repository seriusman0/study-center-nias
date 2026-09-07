<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$role = App\Models\Role::where('name', 'prajurit')->first();
$user = $role->users()->first();
if (!$user) die("No prajurit found\n");

echo "User: " . $user->username . "\n";

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
