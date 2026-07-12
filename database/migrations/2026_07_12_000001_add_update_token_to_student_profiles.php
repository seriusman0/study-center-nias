<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('update_token', 64)->nullable()->unique()->after('catatan_admin');
            $table->timestamp('update_token_expires_at')->nullable()->after('update_token');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['update_token', 'update_token_expires_at']);
        });
    }
};
