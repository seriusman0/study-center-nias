<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_presensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs')->nullOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas_master')->restrictOnDelete();
            $table->date('tanggal');
            $table->time('jam_datang');
            $table->time('jam_pulang');
            $table->unsignedSmallInteger('jumlah_murid')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['mentor_id', 'tanggal']);
            $table->index(['cabang_id', 'tanggal']);
            $table->index(['kelas_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_presensi');
    }
};
