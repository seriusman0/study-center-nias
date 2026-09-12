<?php
$keptIds = [306, 307, 311, 312, 317, 318, 319, 320, 333, 334, 336, 342, 343, 347];

$roleId = 8;
$prajuritIds = DB::table('user_roles')->where('role_id', $roleId)->pluck('user_id')->toArray();
$users = DB::table('users')->whereIn('id', $prajuritIds)->get(['id', 'name']);

$grouped = [];
foreach ($users as $u) {
    $name = trim(strtolower($u->name));
    if (!isset($grouped[$name])) {
        $grouped[$name] = [];
    }
    $grouped[$name][] = $u->id;
}

$toDelete = [];
$toKeepLog = [];

foreach ($grouped as $name => $ids) {
    if (count($ids) > 1) {
        // Find if any is in keptIds
        $keptForThisName = null;
        foreach ($ids as $id) {
            if (in_array($id, $keptIds)) {
                $keptForThisName = $id;
                break;
            }
        }
        
        // If none in keptIds, keep the first one (lowest ID)
        if ($keptForThisName === null) {
            $keptForThisName = min($ids);
        }
        
        $toKeepLog[] = "Keeping $keptForThisName for '$name'";
        
        foreach ($ids as $id) {
            if ($id !== $keptForThisName) {
                $toDelete[] = $id;
            }
        }
    }
}

echo "Users to delete: " . implode(", ", $toDelete) . "\n";

// Now delete them
foreach ($toDelete as $id) {
    // Delete roles
    DB::table('user_roles')->where('user_id', $id)->delete();
    // Delete tokens
    DB::table('personal_access_tokens')->where('tokenable_id', $id)->delete();
    // Delete from users
    DB::table('users')->where('id', $id)->delete();
}

echo "Deleted " . count($toDelete) . " duplicate users.\n";
