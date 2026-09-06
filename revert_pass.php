<?php
$userIds = [74, 151];
foreach($userIds as $id) {
    $user = \App\Models\User::find($id);
    if($user) {
        $user->password = \Illuminate\Support\Facades\Hash::make('12345');
        $user->save();
    }
}
echo "Passwords reverted.\n";
