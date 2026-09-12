<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = DB::table('foo')->where(null, '=', null)->toSql();
echo "SQL: " . $q . "\n";
