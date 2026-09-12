<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

class FixedJurnalLifeCheck extends \App\Models\JurnalLifeCheck {
    protected $table = 'jurnal_life_checks';
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('student_id', $this->student_id)
                     ->where('life_item_id', $this->life_item_id)
                     ->where('tanggal', $this->getOriginal('tanggal') ?? $this->tanggal);
    }
}

$model = new FixedJurnalLifeCheck([
    'student_id' => 1, 'life_item_id' => 1, 'tanggal' => '2026-09-01', 'value' => 5
]);
$model->exists = true;
$model->syncOriginal();

DB::listen(function($query) {
    echo $query->sql . "\n";
});

try {
    $model->fill(['value' => 17])->save();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
