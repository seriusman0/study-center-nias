<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_entries', function (Blueprint $table) {
            $table->string('foto_belajar')->nullable()->after('verse_ref');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_entries', function (Blueprint $table) {
            $table->dropColumn('foto_belajar');
        });
    }
};
