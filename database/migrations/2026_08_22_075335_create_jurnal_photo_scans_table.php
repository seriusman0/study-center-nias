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
        Schema::create('jurnal_photo_scans', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('original_name');
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->json('result_json')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_photo_scans');
    }
};
