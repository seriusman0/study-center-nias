<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nametag_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('width', 4, 1)->default(8.5);
            $table->decimal('height', 4, 1)->default(5.5);
            $table->enum('orientation', ['portrait', 'landscape'])->default('portrait');
            $table->longText('html_content');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nametag_templates');
    }
};
