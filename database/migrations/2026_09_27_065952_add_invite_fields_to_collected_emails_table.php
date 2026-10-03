<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collected_emails', function (Blueprint $table) {
            // Nomor HP dari form landing page
            $table->string('phone', 20)->nullable()->after('email');

            // Timestamp kapan admin menandai sudah diundang ke Google Play Tester group
            // null = belum diundang, ada nilai = sudah diundang
            $table->timestamp('invited_at')->nullable()->after('phone');

            // Siapa admin yang menandai (opsional, untuk audit)
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete()->after('invited_at');

            // Catatan admin (opsional)
            $table->string('invite_note', 255)->nullable()->after('invited_by');
        });
    }

    public function down(): void
    {
        Schema::table('collected_emails', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invited_by');
            $table->dropColumn(['phone', 'invited_at', 'invite_note']);
        });
    }
};
