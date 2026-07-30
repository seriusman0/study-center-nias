<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('college_bible_items', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('id')
                  ->constrained('college_bible_schedules')->cascadeOnDelete();
        });

        // Assign all existing items to schedule id=1 ("Jadwal 1")
        DB::table('college_bible_items')->whereNull('schedule_id')->update(['schedule_id' => 1]);
    }

    public function down(): void
    {
        Schema::table('college_bible_items', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
        });
    }
};
