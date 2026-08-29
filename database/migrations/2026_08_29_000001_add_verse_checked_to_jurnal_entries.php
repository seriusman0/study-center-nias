<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_entries', function (Blueprint $table) {
            // verse_checked: centang hafalan ayat per-hari (terpisah dari verse_ref yang per-minggu)
            $table->boolean('verse_checked')->default(false)->after('verse_ref');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_entries', function (Blueprint $table) {
            $table->dropColumn('verse_checked');
        });
    }
};
