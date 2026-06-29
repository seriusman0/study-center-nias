<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kelas', 100);
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->text('materi');
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->index(['tanggal', 'mentor_id']);
        });

        Schema::create('presensi_students', function (Blueprint $table) {
            $table->foreignId('presensi_id')->constrained('presensi')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->string('keterangan', 255)->nullable();
            $table->primary(['presensi_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_students');
        Schema::dropIfExists('presensi');
    }
};
