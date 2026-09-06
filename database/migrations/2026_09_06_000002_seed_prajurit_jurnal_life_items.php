<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $now = now();
        $items = [
            [
                'kategori'      => 'prajurit',
                'reset_period'  => 'daily',
                'response_type' => 'boolean',
                'label'         => 'Tidak Memaki',
                'is_default'    => true,
                'student_id'    => null,
                'is_active'     => true,
                'created_by'    => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'kategori'      => 'prajurit',
                'reset_period'  => 'daily',
                'response_type' => 'boolean',
                'label'         => 'Membaca Alkitab di Sekolah',
                'is_default'    => true,
                'student_id'    => null,
                'is_active'     => true,
                'created_by'    => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'kategori'      => 'prajurit',
                'reset_period'  => 'daily',
                'response_type' => 'number',   // jumlah salah = angka
                'label'         => 'Jumlah Salah Ayat Hafalan',
                'is_default'    => true,
                'student_id'    => null,
                'is_active'     => true,
                'created_by'    => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];
        DB::table('jurnal_life_items')->insertOrIgnore($items);
    }

    public function down(): void {
        DB::table('jurnal_life_items')
            ->where('kategori', 'prajurit')
            ->whereNull('student_id')
            ->delete();
    }
};
