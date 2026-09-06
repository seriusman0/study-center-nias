<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('jurnal_life_checks', function (Blueprint $table) {
            $table->unsignedSmallInteger('value')->nullable()->after('checked')
                ->comment('Nilai numerik untuk item response_type=number (e.g. jumlah salah ayat)');
        });
    }

    public function down(): void {
        Schema::table('jurnal_life_checks', function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }
};
