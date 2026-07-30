<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('college_bible_config', function (Blueprint $table) {
            $table->foreignId('active_schedule_id')->nullable()->after('id')
                  ->constrained('college_bible_schedules')->nullOnDelete();
        });

        // Set existing config row to use schedule id=1
        DB::table('college_bible_config')->update(['active_schedule_id' => 1]);
    }

    public function down(): void
    {
        Schema::table('college_bible_config', function (Blueprint $table) {
            $table->dropForeign(['active_schedule_id']);
            $table->dropColumn('active_schedule_id');
        });
    }
};
