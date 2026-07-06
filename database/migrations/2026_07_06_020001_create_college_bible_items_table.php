<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('college_bible_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('day_no')->unique();
            $table->string('pl_text')->nullable();
            $table->string('pb_text')->nullable();
            $table->timestamps();
        });

        Schema::create('college_bible_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('anchor_day_no')->default(1);
            $table->date('anchor_date');
            $table->time('form_open_time')->default('06:00:00');
            $table->time('form_close_time')->default('23:59:00');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('college_bible_config');
        Schema::dropIfExists('college_bible_items');
    }
};
