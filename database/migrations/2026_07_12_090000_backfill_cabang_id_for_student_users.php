<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id AND r.name = 'student'
            SET u.cabang_id = 2
            WHERE u.cabang_id IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id AND r.name = 'student'
            SET u.cabang_id = NULL
            WHERE u.cabang_id = 2
        ");
    }
};
