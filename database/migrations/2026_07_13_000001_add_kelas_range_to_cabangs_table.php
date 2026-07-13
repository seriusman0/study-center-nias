<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cabangs', function (Blueprint $table) {
            $table->unsignedTinyInteger('kelas_min')->nullable()->after('pendaftaran_buka');
            $table->unsignedTinyInteger('kelas_max')->nullable()->after('kelas_min');
        });
    }

    public function down(): void
    {
        Schema::table('cabangs', function (Blueprint $table) {
            $table->dropColumn(['kelas_min', 'kelas_max']);
        });
    }
};
