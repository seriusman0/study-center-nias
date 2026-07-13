<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('jurnal_life_items', function (Blueprint $table) {
                $table->string('response_type')->default('check')->after('kategori');
            });
            return;
        }
        DB::statement("ALTER TABLE jurnal_life_items ADD COLUMN response_type ENUM('check','boolean','time_range') NOT NULL DEFAULT 'check' AFTER kategori");
    }

    public function down(): void
    {
        Schema::table('jurnal_life_items', function (Blueprint $table) {
            $table->dropColumn('response_type');
        });
    }
};
