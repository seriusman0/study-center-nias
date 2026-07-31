<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RemajaBeasiswaSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'scholarship_teenager')->first();

        if (!$role) {
            $this->command->error('Role scholarship_teenager not found. Run RoleSeeder first.');
            return;
        }

        $students = [
            ['name' => 'Jan Yustin Solomasi Telaumbanua',   'gender' => 'L', 'phone' => '082195403685', 'grade' => 8],
            ['name' => 'Magdalena Telaumbanua',              'gender' => 'P', 'phone' => '082255746539', 'grade' => 8],
            ['name' => 'Marcel Saputra Telaumbanua',         'gender' => 'L', 'phone' => '082255746417', 'grade' => 8],
            ['name' => 'Rafael Sebastisn Daeli',             'gender' => 'L', 'phone' => '085361611137', 'grade' => 8],
            ['name' => 'Rut Clara Nduru',                    'gender' => 'P', 'phone' => '081312251516', 'grade' => 8],
            ['name' => 'Walmond Elvis Hanofanolo Zendrato',  'gender' => 'L', 'phone' => '081361507201', 'grade' => 8],
            ['name' => 'Andi Elman Jaya Tel',                'gender' => 'L', 'phone' => '085142243779', 'grade' => 9],
            ['name' => 'Jessica Sara Ester Ndruru',          'gender' => 'P', 'phone' => '082260727027', 'grade' => 9],
            ['name' => 'Rifandy Yosua Gea',                  'gender' => 'L', 'phone' => '082267281958', 'grade' => 9],
            ['name' => 'I Vander Tel',                       'gender' => 'L', 'phone' => null,           'grade' => 9],
            ['name' => 'Rosmawati Telaumbanua',              'gender' => 'P', 'phone' => '082255746539', 'grade' => 10],
            ['name' => 'Amel Permata Sari Telaumbanua',      'gender' => 'P', 'phone' => '082255746417', 'grade' => 10],
            ['name' => 'Andika Cristian Telaumbanua',        'gender' => 'L', 'phone' => '085274822486', 'grade' => 10],
            ['name' => 'Markus Gea',                         'gender' => 'L', 'phone' => '081360711523', 'grade' => 10],
            ['name' => 'Yarni Kasih Hia',                    'gender' => 'P', 'phone' => '082331748370', 'grade' => 10],
            ['name' => 'Kezia Nonifili Harefa',              'gender' => 'P', 'phone' => '081265594785', 'grade' => 10],
            ['name' => 'Daniel Hendrata Zebua',              'gender' => 'L', 'phone' => '082370927254', 'grade' => 10],
        ];

        foreach ($students as $data) {
            $slug     = Str::slug($data['name'], '.');
            $email    = $slug . '@remaja.beasiswa.local';
            $username = Str::slug($data['name'], '-');

            $user = User::where('email', $email)
                ->orWhere('username', $username)
                ->first();

            if ($user) {
                $user->update([
                    'name'              => $data['name'],
                    'email'             => $email,
                    'username'          => $username,
                    'password'          => Hash::make('12345'),
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user = User::create([
                    'name'              => $data['name'],
                    'email'             => $email,
                    'username'          => $username,
                    'password'          => Hash::make('12345'),
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]);
            }

            if (!$user->hasRole('scholarship_teenager')) {
                $user->roles()->attach($role->id);
            }

            StudentProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'gender'        => $data['gender'],
                    'student_phone' => $data['phone'],
                    'grade_class'   => 'Kelas ' . $data['grade'],
                ]
            );
        }
    }
}
