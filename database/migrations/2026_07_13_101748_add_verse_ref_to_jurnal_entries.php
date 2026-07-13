<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jurnal_entries', function (Blueprint $table) {
            $table->string('verse_ref', 100)->nullable()->after('verse_week_key');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_entries', function (Blueprint $table) {
            $table->dropColumn('verse_ref');
        });
    }
};
