<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("
                UPDATE users SET cabang_id = 2
                WHERE cabang_id IS NULL AND id IN (
                    SELECT ur.user_id FROM user_roles ur
                    INNER JOIN roles r ON r.id = ur.role_id AND r.name = 'student'
                )
            ");
            return;
        }
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
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("
                UPDATE users SET cabang_id = NULL
                WHERE cabang_id = 2 AND id IN (
                    SELECT ur.user_id FROM user_roles ur
                    INNER JOIN roles r ON r.id = ur.role_id AND r.name = 'student'
                )
            ");
            return;
        }
        DB::statement("
            UPDATE users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id AND r.name = 'student'
            SET u.cabang_id = NULL
            WHERE u.cabang_id = 2
        ");
    }
};
