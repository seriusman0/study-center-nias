<?php
$file = 'routes/web.php';
$content = file_get_contents($file);

$useStatement = "use App\Http\Controllers\Web\PublicJurnalController;\n";
if (strpos($content, 'PublicJurnalController') === false) {
    $content = preg_replace('/use App\\\\Http\\\\Controllers\\\\Web\\\\HomeController;/', $useStatement . 'use App\Http\Controllers\Web\HomeController;', $content);
}

$routeBlock = <<<PHP

// Public Jurnal API for Scanner
Route::post('/public-jurnal/scan', [PublicJurnalController::class, 'scan'])->name('public-jurnal.scan');
Route::post('/public-jurnal/save', [PublicJurnalController::class, 'save'])->name('public-jurnal.save');

PHP;

if (strpos($content, '/public-jurnal/scan') === false) {
    // Insert after Route::get('/', [HomeController::class, 'index'])->name('home');
    $content = preg_replace('/Route::get\(\'\/\', \[HomeController::class, \'index\'\]\)->name\(\'home\'\);/', "Route::get('/', [HomeController::class, 'index'])->name('home');\n" . $routeBlock, $content);
    file_put_contents($file, $content);
    echo "Patched routes.";
} else {
    echo "Routes already exist.";
}
