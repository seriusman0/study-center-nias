<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend enum to support college-specific categories
        DB::statement("ALTER TABLE jurnal_life_items MODIFY COLUMN kategori ENUM('kerohanian','pendidikan','karakter','pembacaan','sidang','rohani') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE jurnal_life_items MODIFY COLUMN kategori ENUM('kerohanian','pendidikan','karakter') NOT NULL");
    }
};
