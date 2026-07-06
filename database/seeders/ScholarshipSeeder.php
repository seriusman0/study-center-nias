<?php

namespace Database\Seeders;

use App\Models\CollegeProfile;
use App\Models\JurnalLifeItem;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ScholarshipSeeder extends Seeder
{
    public function run(): void
    {
        $collegeRole = Role::where('name', 'college')->first();
        $studentRole = Role::where('name', 'student')->first();

        // These 5 are college role users (bukan student)
        $collegeUsers = [
            [
                'name' => 'Jelita Efni Telaumbanua',
                'email' => 'jelita.efni@example.com',
                'profile' => ['gender' => 'P', 'campus_name' => 'UNIAS', 'current_semester' => 5, 'address' => 'Sifalaete Ulu'],
            ],
            [
                'name' => 'Karunia Plus',
                'email' => 'karunia.plus@example.com',
                'profile' => ['gender' => 'L', 'campus_name' => 'STT Shalom', 'current_semester' => 3, 'address' => 'Hilihao'],
            ],
            [
                'name' => 'Ricard Ilfans Ndruru',
                'email' => 'ricard.ilfans@example.com',
                'profile' => ['gender' => 'L', 'campus_name' => 'UNIAS', 'current_semester' => 3, 'address' => 'Togizita - Jln. Golkar'],
            ],
            [
                'name' => 'Lince Kristi Hulu',
                'email' => 'lince.kristi@example.com',
                'profile' => ['gender' => 'P', 'campus_name' => 'UNIAS', 'current_semester' => 8, 'address' => 'Lasara Bahili - Mudik'],
            ],
            [
                'name' => 'Dina Riang Ziliwu',
                'email' => 'dina.riang@example.com',
                'profile' => ['gender' => 'P', 'campus_name' => 'STT Shalom', 'current_semester' => 1, 'address' => null],
            ],
        ];

        // College life items to auto-assign (template items)
        $collegeLifeItems = $this->seedCollegeLifeItems();

        foreach ($collegeUsers as $data) {
            $username = strtolower(str_replace(' ', '', $data['name']));

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'               => $data['name'],
                    'username'           => $username,
                    'password'           => Hash::make('12345'),
                    'is_active'          => true,
                    'email_verified_at'  => now(),
                ]
            );

            // Remove student role if exists, ensure college role
            if ($studentRole) {
                $user->roles()->detach($studentRole->id);
            }
            if ($collegeRole && !$user->hasRole('college')) {
                $user->roles()->attach($collegeRole->id);
            }

            // Remove student profile, create college profile
            StudentProfile::where('user_id', $user->id)->delete();
            CollegeProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'institution_name' => $data['profile']['campus_name'],
                    'position'         => null,
                ]
            );

            // Auto-assign college life items
            foreach ($collegeLifeItems as $item) {
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

        // Koordinator accounts (admins for each campus - separate from the 5 college users)
        $admins = [
            ['name' => 'Koordinator UNIAS',      'email' => 'koordinator.unias@example.com',  'institution' => 'UNIAS'],
            ['name' => 'Koordinator STT Shalom', 'email' => 'koordinator.shalom@example.com', 'institution' => 'STT Shalom'],
        ];

        foreach ($admins as $data) {
            $username = strtolower(str_replace(' ', '', $data['name']));

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'username'          => $username,
                    'password'          => Hash::make('12345'),
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );

            if ($collegeRole && !$user->hasRole('college')) {
                $user->roles()->attach($collegeRole->id);
            }

            CollegeProfile::updateOrCreate(
                ['user_id' => $user->id],
                ['institution_name' => $data['institution']]
            );
        }
    }

    private function seedCollegeLifeItems(): \Illuminate\Support\Collection
    {
        $now = now();

        // Categories: 'pembacaan', 'sidang', 'rohani'
        // [kategori, label, response_type]
        $items = [
            ['pembacaan', 'Perjanjian Lama',                              'check'],
            ['pembacaan', 'Perjanjian Baru',                              'check'],
            ['pembacaan', 'Upload Pembacaan Alkitab di Group',            'boolean'],
            ['rohani',    'Baca Buku Rohani (1 Bab / 1 Judul per Minggu)', 'boolean'],
            ['sidang',    'SPR',                                          'check'],
            ['sidang',    'Sidang Remaja',                                'check'],
            ['sidang',    'Sidang Kelompok',                              'check'],
            ['sidang',    'Sidang Doa',                                   'check'],
            ['sidang',    'Sidang Saudari',                               'check'],
            ['sidang',    'Sidang Pemuda',                                'check'],
            ['sidang',    'Sidang Spesial (Seminar / Sidang Khusus)',     'check'],
            ['sidang',    'Sharing di Sidang SPR',                        'boolean'],
            ['rohani',    'Buku Catatan',                                 'boolean'],
            ['rohani',    'Doa saat SPR',                                 'boolean'],
            ['rohani',    'Belajar',                                      'time_range'],
            ['rohani',    'Pelayanan',                                    'check'],
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
