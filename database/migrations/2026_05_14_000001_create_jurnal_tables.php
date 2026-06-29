<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_bible_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('pl_porsi', 255)->nullable();
            $table->string('pb_porsi', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('jurnal_weekly_verses', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->tinyInteger('minggu');
            $table->string('referensi', 100);
            $table->text('isi');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['tahun', 'bulan', 'minggu']);
        });

        Schema::create('jurnal_life_items', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['kerohanian', 'pendidikan', 'karakter']);
            $table->string('label', 150);
            $table->boolean('is_default')->default(false);
            $table->foreignId('student_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['kategori', 'is_active']);
            $table->index('student_id');
        });

        Schema::create('jurnal_student_life_items', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('life_item_id')->constrained('jurnal_life_items')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['student_id', 'life_item_id']);
        });

        Schema::create('jurnal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs')->nullOnDelete();
            $table->date('tanggal');
            $table->boolean('pl_checked')->default(false);
            $table->boolean('pb_checked')->default(false);
            $table->string('verse_week_key', 12)->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'tanggal']);
            $table->index(['cabang_id', 'tanggal']);
        });

        Schema::create('jurnal_life_checks', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('life_item_id')->constrained('jurnal_life_items')->cascadeOnDelete();
            $table->date('tanggal');
            $table->boolean('checked')->default(true);
            $table->timestamps();
            $table->primary(['student_id', 'life_item_id', 'tanggal']);
            $table->index(['student_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_life_checks');
        Schema::dropIfExists('jurnal_entries');
        Schema::dropIfExists('jurnal_student_life_items');
        Schema::dropIfExists('jurnal_life_items');
        Schema::dropIfExists('jurnal_weekly_verses');
        Schema::dropIfExists('jurnal_bible_schedules');
    }
};
