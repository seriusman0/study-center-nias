<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CabangSeeder extends Seeder
{
    public function run(): void
    {
        $cabangs = [
            ['nama' => 'Gunungsitoli', 'slug' => 'gunungsitoli', 'alamat' => 'Kota Gunungsitoli, Nias', 'kontak' => null],
            ['nama' => 'Kabupaten Nias', 'slug' => 'kabupaten-nias', 'alamat' => 'Kabupaten Nias', 'kontak' => null],
            ['nama' => 'Kabupaten Nias Selatan', 'slug' => 'nias-selatan', 'alamat' => 'Kabupaten Nias Selatan', 'kontak' => null],
            ['nama' => 'Kabupaten Nias Utara', 'slug' => 'nias-utara', 'alamat' => 'Kabupaten Nias Utara', 'kontak' => null],
        ];

        foreach ($cabangs as $cabang) {
            DB::table('cabangs')->updateOrInsert(['slug' => $cabang['slug']], $cabang);
        }
    }
}
