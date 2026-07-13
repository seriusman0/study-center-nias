<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_pelajarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('mata_pelajarans')->insert([
            ['nama' => 'MATEMATIKA',     'urutan' => 1, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'BAHASA INGGRIS', 'urutan' => 2, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'BAHASA MANDARIN','urutan' => 3, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'KOMPUTER',       'urutan' => 4, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_pelajarans');
    }
};
