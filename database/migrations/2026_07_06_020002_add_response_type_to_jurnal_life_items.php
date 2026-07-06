<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE jurnal_life_items ADD COLUMN response_type ENUM('check','boolean','time_range') NOT NULL DEFAULT 'check' AFTER kategori");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE jurnal_life_items DROP COLUMN response_type");
    }
};
