<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$usersData = [
    ['nama' => 'yozia epalman gulo', 'kelas' => '6', 'sekolah' => 'sd negeri 071068 dekha lahemo', 'tanggal_lahir' => '2015-03-29', 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'yazdan rafael gulo', 'kelas' => '3', 'sekolah' => 'sd negeri 071068 dekha lahemo', 'tanggal_lahir' => '2018-08-14', 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'yuken hardin jaya waruwu', 'kelas' => '6', 'sekolah' => 'sd negri 071068 dekha lahemo', 'tanggal_lahir' => null, 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'elisa ndraha', 'kelas' => '5', 'sekolah' => 'sd negeri 071068 dekha lahemo', 'tanggal_lahir' => null, 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'ayu siska ndraha', 'kelas' => '7', 'sekolah' => 'smp negeri 4 gido', 'tanggal_lahir' => null, 'jenis_kelamin' => 'Perempuan'],
    ['nama' => 'inez julistyn waruwu', 'kelas' => '6', 'sekolah' => 'sd negeri 071068 dekha lahemo', 'tanggal_lahir' => '2015-07-17', 'jenis_kelamin' => 'Perempuan'],
    ['nama' => 'miseri kordias domini ndraha', 'kelas' => '6', 'sekolah' => 'sd negeri 071068 dekha lahemo', 'tanggal_lahir' => null, 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'sultan putra agian gulo', 'kelas' => '7', 'sekolah' => 'smp negeri 4 gido', 'tanggal_lahir' => null, 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'andrian pril faomasi waruwu', 'kelas' => '5', 'sekolah' => 'sd negeri 071068 dekha lahemo', 'tanggal_lahir' => null, 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'krisman jaya ndraha', 'kelas' => '5', 'sekolah' => 'sd negeri 071068 dekha lahemo', 'tanggal_lahir' => null, 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'kesya celementine waruwu', 'kelas' => '7', 'sekolah' => 'smp negeri 4 gido', 'tanggal_lahir' => null, 'jenis_kelamin' => 'Perempuan'],
    ['nama' => 'leliser waruwu', 'kelas' => '4', 'sekolah' => 'sd negeri 071068 dekha lahemo', 'tanggal_lahir' => null, 'jenis_kelamin' => 'Perempuan'],
    ['nama' => 'helsa donelia waruwu', 'kelas' => '3', 'sekolah' => null, 'tanggal_lahir' => null, 'jenis_kelamin' => 'Perempuan'],
    ['nama' => 'meiman boi saputra waruwu', 'kelas' => '6', 'sekolah' => null, 'tanggal_lahir' => null, 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'grasela waruwu', 'kelas' => '3', 'sekolah' => null, 'tanggal_lahir' => null, 'jenis_kelamin' => 'Perempuan'],
    ['nama' => 'imel elvianis waruwu', 'kelas' => '6', 'sekolah' => null, 'tanggal_lahir' => null, 'jenis_kelamin' => 'Perempuan'],
    ['nama' => 'okto ndraha', 'kelas' => '4', 'sekolah' => null, 'tanggal_lahir' => null, 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'julfan ndraha', 'kelas' => '2', 'sekolah' => null, 'tanggal_lahir' => null, 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'farel waruwu', 'kelas' => '3', 'sekolah' => null, 'tanggal_lahir' => null, 'jenis_kelamin' => 'Laki-laki'],
    ['nama' => 'jelita gulo', 'kelas' => '4', 'sekolah' => null, 'tanggal_lahir' => null, 'jenis_kelamin' => 'Perempuan'],
];

$role = DB::table('roles')->where('name', 'prajurit')->first();
if (!$role) {
    echo "Role 'prajurit' not found!\n";
    exit;
}

$createdCount = 0;

foreach ($usersData as $data) {
    $name = ucwords(strtolower($data['nama']));
    $username = Str::slug($name) . rand(100, 999);
    $email = $username . '@example.com';
    
    $userId = DB::table('users')->insertGetId([
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'password' => Hash::make('password123'),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('user_roles')->insert([
        'user_id' => $userId,
        'role_id' => $role->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('student_profiles')->insert([
        'user_id' => $userId,
        'birth_date' => $data['tanggal_lahir'],
        'gender' => $data['jenis_kelamin'],
        'school_name' => $data['sekolah'],
        'grade_class' => $data['kelas'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $createdCount++;
}

echo "Successfully created $createdCount prajurit users.\n";
