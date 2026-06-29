<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas_master', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['nama', 'cabang_id']);
            $table->index(['cabang_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_master');
    }
};
