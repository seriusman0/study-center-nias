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
        Schema::create('scholarship_journal_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('scholarship_journals')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->enum('file_type', ['transkrip_khs', 'sertifikat', 'foto_kegiatan', 'lainnya'])->default('lainnya');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_journal_attachments');
    }
};
