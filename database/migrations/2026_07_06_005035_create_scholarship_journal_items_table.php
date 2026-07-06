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
        Schema::create('scholarship_journal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->unique()->constrained('scholarship_journals')->cascadeOnDelete();
            // Akademik
            $table->decimal('gpa_current_semester', 3, 2)->nullable();
            $table->decimal('cumulative_gpa', 3, 2)->nullable();
            $table->text('academic_summary')->nullable();
            $table->tinyInteger('class_attendance_percentage')->unsigned()->nullable();
            // Aktivitas
            $table->text('organization_activities')->nullable();
            $table->text('training_seminars')->nullable();
            $table->text('achievements')->nullable();
            // Pelayanan
            $table->text('community_service_details')->nullable();
            $table->unsignedSmallInteger('service_hours')->nullable();
            // Refleksi
            $table->text('personal_reflection')->nullable();
            $table->text('next_month_goals')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_journal_items');
    }
};
