<?php

namespace Database\Seeders;

use App\Models\JurnalLifeItem;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        $lifeItems = $this->seedLifeItems();

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

            // Remove excluded items that may have been assigned previously
            $excludedIds = DB::table('jurnal_life_items')
                ->whereNull('student_id')
                ->whereIn('label', [
                    'Baca Buku Rohani (1 Bab / 1 Judul per Minggu)',
                    'Sidang Saudari',
                    'Sidang Pemuda',
                ])
                ->pluck('id');

            if ($excludedIds->isNotEmpty()) {
                DB::table('jurnal_student_life_items')
                    ->where('student_id', $user->id)
                    ->whereIn('life_item_id', $excludedIds)
                    ->delete();
            }

            // Assign the 13 approved items
            foreach ($lifeItems as $item) {
                $exists = DB::table('jurnal_student_life_items')
                    ->where('student_id', $user->id)
                    ->where('life_item_id', $item->id)
                    ->exists();

                if (!$exists) {
                    DB::table('jurnal_student_life_items')->insert([
                        'student_id'   => $user->id,
                        'life_item_id' => $item->id,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }
    }

    private function seedLifeItems(): \Illuminate\Support\Collection
    {
        $now = now();

        // Same as college items MINUS:
        // - "Baca Buku Rohani (1 Bab / 1 Judul per Minggu)"
        // - "Sidang Saudari"
        // - "Sidang Pemuda"
        $items = [
            ['pembacaan', 'Perjanjian Lama',                          'check'],
            ['pembacaan', 'Perjanjian Baru',                          'check'],
            ['pembacaan', 'Upload Pembacaan Alkitab di Group',        'boolean'],
            ['sidang',    'SPR',                                      'check'],
            ['sidang',    'Sidang Remaja',                            'check'],
            ['sidang',    'Sidang Kelompok',                          'check'],
            ['sidang',    'Sidang Doa',                               'check'],
            ['sidang',    'Sidang Spesial (Seminar / Sidang Khusus)', 'check'],
            ['sidang',    'Sharing di Sidang SPR',                    'boolean'],
            ['rohani',    'Buku Catatan',                             'boolean'],
            ['rohani',    'Doa saat SPR',                             'boolean'],
            ['rohani',    'Belajar',                                  'time_range'],
            ['rohani',    'Pelayanan',                                'check'],
        ];

        $ids = [];
        foreach ($items as [$kategori, $label, $responseType]) {
            DB::table('jurnal_life_items')->updateOrInsert(
                ['kategori' => $kategori, 'label' => $label, 'student_id' => null],
                ['response_type' => $responseType, 'is_default' => false, 'is_active' => true, 'created_by' => null, 'updated_at' => $now, 'created_at' => $now]
            );
            $ids[] = DB::table('jurnal_life_items')
                ->where('kategori', $kategori)
                ->where('label', $label)
                ->whereNull('student_id')
                ->value('id');
        }

        return JurnalLifeItem::whereIn('id', $ids)->get();
    }
}
