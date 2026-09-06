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

class StudentBatch2Seeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'scholarship_teenager')->first();

        if (!$role) {
            $this->command->error('Role scholarship_teenager not found. Run RoleSeeder first.');
            return;
        }

        $cabang = DB::table('cabangs')->where('slug', 'gunungsitoli')->first();

        if (!$cabang) {
            $this->command->error('Cabang gunungsitoli not found. Run CabangSeeder first.');
            return;
        }

        $students = [
            'Amin Rais Hulu',
            'Billy Fernando Christian Panjaitan',
            'David Daniel Gea',
            'Dion Ivandy Lase',
            'Dwi Cahyani Harefa',
            'Felly Permata Sari Bu\'ulolo',
            'Jansen Sendroro Ndruru',
            'Jesyca Putri Rosa Lase',
            'Keyla Rizki Huli',
            'Kezya Princess Harefa',
            'Mersi Ndraha',
            'Nindy Vineeta Serti Zai',
            'Poppy Marisah Gea',
            'Sinema Sharfat Waristo Zebua',
            'Winny Eviyanti Sasmita Ndruru',
            'Aura Stephany Gimelda',
            'Rostina Telaumbanua',
            'Riang Destari Tafonao',
            'Joylin Flora Lahagu',
            'Andriel Oinitehe Zega',
            'Calista Vilcia Ndraha',
            'Amel Avrilian Zebua',
            'Rafael Daeli',
            'Niat Berliana Zai',
            // Tambahan Jurnal Remaja Beasiswa
            'Rafael Zebua',
            'Darwan Zendrato',
        ];

        $lifeItems = $this->seedLifeItems();

        $excludedIds = DB::table('jurnal_life_items')
            ->whereNull('student_id')
            ->whereIn('label', [
                'Baca Buku Rohani (1 Bab / 1 Judul per Minggu)',
                'Sidang Saudari',
                'Sidang Pemuda',
            ])
            ->pluck('id');

        foreach ($students as $name) {
            $slug     = Str::slug($name, '.');
            $email    = $slug . '@remaja.beasiswa.local';
            $username = Str::slug($name, '-');

            $user = User::where('email', $email)
                ->orWhere('username', $username)
                ->first();

            if ($user) {
                $user->update([
                    'name'              => $name,
                    'email'             => $email,
                    'username'          => $username,
                    'cabang_id'         => $cabang->id,
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user = User::create([
                    'name'              => $name,
                    'email'             => $email,
                    'username'          => $username,
                    'password'          => Hash::make('12345'),
                    'cabang_id'         => $cabang->id,
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]);
            }

            if (!$user->hasRole('scholarship_teenager')) {
                $user->roles()->attach($role->id);
            }

            StudentProfile::updateOrCreate(
                ['user_id' => $user->id],
                []
            );

            if ($excludedIds->isNotEmpty()) {
                DB::table('jurnal_student_life_items')
                    ->where('student_id', $user->id)
                    ->whereIn('life_item_id', $excludedIds)
                    ->delete();
            }

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

            $this->command->info("Seeded: {$name} ({$username})");
        }
    }

    private function seedLifeItems(): \Illuminate\Support\Collection
    {
        $now = now();

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
