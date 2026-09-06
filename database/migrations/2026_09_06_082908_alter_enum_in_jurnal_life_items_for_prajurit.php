<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE jurnal_life_items MODIFY COLUMN kategori ENUM('kerohanian','pendidikan','karakter','pembacaan','sidang','rohani','prajurit') NOT NULL");
        DB::statement("ALTER TABLE jurnal_life_items MODIFY COLUMN response_type ENUM('check','boolean','time_range','number') NOT NULL DEFAULT 'check'");

        DB::table('jurnal_life_items')->where('label', 'Tidak Memaki')->update(['kategori' => 'prajurit', 'response_type' => 'boolean']);
        DB::table('jurnal_life_items')->where('label', 'Membaca Alkitab di Sekolah')->update(['kategori' => 'prajurit', 'response_type' => 'boolean']);
        DB::table('jurnal_life_items')->where('label', 'Jumlah Salah Ayat Hafalan')->update(['kategori' => 'prajurit', 'response_type' => 'number']);
    }

    public function down(): void
    {
    }
};
