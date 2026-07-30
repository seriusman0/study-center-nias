<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cabangs', function (Blueprint $table) {
            $table->unsignedBigInteger('bible_schedule_id')->nullable()->after('mata_pelajaran');
            $table->foreign('bible_schedule_id')->references('id')->on('college_bible_schedules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cabangs', function (Blueprint $table) {
            $table->dropForeign(['bible_schedule_id']);
            $table->dropColumn('bible_schedule_id');
        });
    }
};
