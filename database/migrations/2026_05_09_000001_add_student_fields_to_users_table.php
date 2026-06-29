<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->after('bio');
            $table->string('tempat_lahir')->nullable()->after('tanggal_lahir');
            $table->string('no_hp_orangtua')->nullable()->after('tempat_lahir');
            $table->string('nama_sekolah')->nullable()->after('no_hp_orangtua');
            $table->text('alamat')->nullable()->after('nama_sekolah');
            $table->unsignedSmallInteger('tahun_masuk')->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_lahir',
                'tempat_lahir',
                'no_hp_orangtua',
                'nama_sekolah',
                'alamat',
                'tahun_masuk',
            ]);
            $table->string('email')->nullable(false)->change();
        });
    }
};
