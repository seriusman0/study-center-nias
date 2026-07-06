<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('college_study_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('life_item_id')->constrained('jurnal_life_items')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->enum('tipe', ['mandiri', 'kelompok'])->default('mandiri');
            $table->timestamps();
            $table->unique(['user_id', 'life_item_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('college_study_logs');
    }
};
