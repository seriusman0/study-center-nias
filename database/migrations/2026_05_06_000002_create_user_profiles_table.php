<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // instagram, whatsapp, email, facebook
            $table->string('value');
            $table->timestamps();
            $table->unique(['user_id', 'platform']);
        });

        Schema::create('cv_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('pendidikan')->nullable();
            $table->json('pengalaman')->nullable();
            $table->json('keterampilan')->nullable();
            $table->text('portofolio')->nullable();
            $table->string('template')->default('template1');
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('cv_data');
        Schema::dropIfExists('user_social_links');
    }
};
