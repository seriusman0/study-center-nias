<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_life_items', function (Blueprint $table) {
            // daily = reset every day; weekly_saturday = resets each Saturday; weekly_sunday = resets each Sunday
            $table->string('reset_period', 20)->default('daily')->after('response_type');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_life_items', function (Blueprint $table) {
            $table->dropColumn('reset_period');
        });
    }
};
