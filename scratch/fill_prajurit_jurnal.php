<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\JurnalLifeItem;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalEntry;
use Carbon\Carbon;

$roleId = Role::where('name', 'prajurit')->value('id');
$prajurits = User::where('is_active', true)
    ->whereHas('roles', function($q) use ($roleId) {
        $q->where('roles.id', $roleId);
    })->get();

$items = JurnalLifeItem::where('kategori', 'prajurit')
    ->where('is_active', true)
    ->get();

$startDate = Carbon::create(2026, 9, 6);
$endDate = Carbon::create(2026, 9, 12); // Today

$mabId = JurnalLifeItem::where('label', 'like', '%Membaca Alkitab Bersama-sama%')->value('id');

DB::beginTransaction();
try {
    foreach ($prajurits as $user) {
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $tanggal = $d->toDateString();
            
            // Create JurnalEntry
            $entry = JurnalEntry::updateOrCreate(
                ['student_id' => $user->id, 'tanggal' => $tanggal],
                [
                    'cabang_id' => $user->cabang_id,
                    'pl_checked' => true,
                    'pb_checked' => true
                ]
            );

            // Create checks
            foreach ($items as $item) {
                $value = null;
                if ($item->response_type === 'number') {
                    // Randomize so it doesn't look like 17 every day
                    $value = rand(10, 25);
                }

                JurnalLifeCheck::updateOrCreate(
                    [
                        'student_id' => $user->id,
                        'life_item_id' => $item->id,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'checked' => true,
                        'value' => $value,
                    ]
                );
            }
        }
    }
    DB::commit();
    echo "Successfully filled data for " . count($prajurits) . " prajurits from 6 to 12 Sept.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
