<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cabangs', function (Blueprint $table) {
            $table->json('mata_pelajaran')->nullable()->after('kelas_max');
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->json('mata_pelajaran')->nullable()->after('note');
        });

        // Backfill default subjects for existing cabangs
        DB::table('cabangs')->whereNull('mata_pelajaran')->update([
            'mata_pelajaran' => json_encode(['Matematika', 'B.Inggris', 'B.Mandarin', 'Komputer']),
        ]);
    }

    public function down(): void
    {
        Schema::table('cabangs', function (Blueprint $table) {
            $table->dropColumn('mata_pelajaran');
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn('mata_pelajaran');
        });
    }
};
