<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurnalLifeItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['kerohanian', 'Mengawali hari dengan berdoa',        'daily'],
            ['pendidikan', 'Hadir di kelas SC',                   'daily'],
            ['pendidikan', 'Hadir pembinaan hari Sabtu',          'weekly_saturday'],
            ['pendidikan', 'Hadir pembinaan hari Minggu',         'weekly_sunday'],
            ['karakter',   'Merapikan tempat tidur',              'daily'],
            ['karakter',   'Menyapa orangtua/guru/kakak',         'daily'],
        ];

        $now = now();

        foreach ($items as [$kategori, $label, $resetPeriod]) {
            DB::table('jurnal_life_items')->updateOrInsert(
                ['kategori' => $kategori, 'label' => $label, 'student_id' => null],
                [
                    'reset_period' => $resetPeriod,
                    'is_default'   => true,
                    'is_active'    => true,
                    'created_by'   => null,
                    'updated_at'   => $now,
                    'created_at'   => $now,
                ]
            );
        }
    }
}
