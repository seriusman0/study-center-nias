<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurnalLifeItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['kerohanian', 'Mengawali hari dengan berdoa'],
            ['kerohanian', 'Baca Alkitab'],
            ['kerohanian', 'Hafal Ayat'],
            ['pendidikan', 'Hadir di kelas SC'],
            ['pendidikan', 'Hadir Ibadah hari sabtu'],
            ['pendidikan', 'Hadir ibadah hari minggu'],
            ['karakter',   'Merapikan tempat tidur'],
            ['karakter',   'Menyapa orangtua/guru/kakak'],
        ];

        $now = now();

        foreach ($items as [$kategori, $label]) {
            DB::table('jurnal_life_items')->updateOrInsert(
                ['kategori' => $kategori, 'label' => $label, 'student_id' => null],
                [
                    'is_default' => true,
                    'is_active'  => true,
                    'created_by' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
