<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasMasterSeeder extends Seeder
{
    public function run(): void
    {
        $rows = DB::table('presensi')
            ->whereNotNull('kelas')
            ->whereNotNull('cabang_id')
            ->select('kelas', 'cabang_id')
            ->distinct()
            ->get();

        $now = now();

        foreach ($rows as $r) {
            $nama = trim((string) $r->kelas);
            if ($nama === '') continue;

            DB::table('kelas_master')->updateOrInsert(
                ['nama' => $nama, 'cabang_id' => $r->cabang_id],
                [
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $masterId = DB::table('kelas_master')
                ->where('nama', $nama)
                ->where('cabang_id', $r->cabang_id)
                ->value('id');

            if ($masterId) {
                DB::table('presensi')
                    ->where('cabang_id', $r->cabang_id)
                    ->where('kelas', $r->kelas)
                    ->whereNull('kelas_id')
                    ->update(['kelas_id' => $masterId]);
            }
        }
    }
}
