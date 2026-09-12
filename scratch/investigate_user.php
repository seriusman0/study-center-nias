<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'LIKE', '%andrian pril%')->first();
if ($user) {
    echo "Found User: {$user->name} (ID: {$user->id}, Role: {$user->role})\n";
    
    $entries = \App\Models\JurnalEntry::where('user_id', $user->id)->count();
    $lifeChecks = \App\Models\JurnalLifeCheck::where('user_id', $user->id)->count();
    
    echo "Jurnal Entries: $entries\n";
    echo "Jurnal Life Checks: $lifeChecks\n";
    
    $prajurit = \App\Models\User::where('role', 'prajurit')->whereHas('lifeChecks')->first();
    if (!$prajurit) {
        $prajurit = \App\Models\User::where('role', 'prajurit')->where('id', '!=', $user->id)->first();
    }
    
    if ($prajurit) {
        echo "Found Prajurit: {$prajurit->name} (ID: {$prajurit->id})\n";
        $latest = \App\Models\JurnalLifeCheck::where('user_id', $prajurit->id)->orderBy('date', 'desc')->first();
        if ($latest) {
            echo "Sample Life Check for Prajurit:\n";
            print_r($latest->toArray());
            
            $items = \App\Models\JurnalLifeItem::where('jurnal_life_check_id', $latest->id)->get();
            echo "Items count: " . $items->count() . "\n";
        } else {
            echo "No life check for this prajurit.\n";
            
            // let's just find ANY life check
            $any = \App\Models\JurnalLifeCheck::first();
            if ($any) {
                echo "Found ANY life check:\n";
                print_r($any->toArray());
            }
        }
    }
} else {
    echo "User Andrian not found.\n";
}
