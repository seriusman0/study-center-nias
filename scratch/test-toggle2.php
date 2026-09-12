<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('username', 'yoziaepalmangulo')->first();

$request = \Illuminate\Http\Request::create('/jurnal-prajurit/toggle', 'POST', [
    'type' => 'life',
    'item_id' => 1,
    'checked' => true,
    'date' => date('Y-m-d')
]);
$request->headers->set('Accept', 'application/json');
$request->setUserResolver(function() use ($user) { return $user; });

$controller = new \App\Http\Controllers\Web\Prajurit\PrajuritJurnalController();
try {
    $response = $controller->toggle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
