<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('college_bible_items', function (Blueprint $table) {
            $table->dropUnique('college_bible_items_day_no_unique');
            $table->unique(['schedule_id', 'day_no']);
        });
    }

    public function down(): void
    {
        Schema::table('college_bible_items', function (Blueprint $table) {
            $table->dropUnique(['schedule_id', 'day_no']);
            $table->unique('day_no');
        });
    }
};
