<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issued_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sertifikat', 80)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('certificate_templates')->restrictOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_lulus');
            $table->string('nama_kursus', 150);
            $table->string('file_path', 500);
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->index('user_id');
            $table->index('template_id');
            $table->index('issued_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issued_certificates');
    }
};
