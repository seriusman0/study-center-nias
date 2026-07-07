<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('student_phone', 20)->nullable()->after('guardian_phone');
            $table->string('photo')->nullable()->after('student_phone');
            $table->text('note')->nullable()->after('photo');
            $table->boolean('is_pending')->default(true)->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['student_phone', 'photo', 'note', 'is_pending']);
        });
    }
};
