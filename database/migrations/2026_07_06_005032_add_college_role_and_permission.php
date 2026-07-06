<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $collegeRole = DB::table('roles')->where('name', 'college')->first();
        if (!$collegeRole) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'college',
                'description' => 'Koordinator Perguruan Tinggi / Pengelola Beasiswa',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $roleId = $collegeRole->id;
        }

        $permission = DB::table('permissions')->where('name', 'review_journals')->first();
        if (!$permission) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'review_journals',
                'description' => 'Meninjau dan memverifikasi jurnal mahasiswa',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $permissionId = $permission->id;
        }

        $exists = DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->exists();

        if (!$exists) {
            DB::table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function down(): void
    {
        $role = DB::table('roles')->where('name', 'college')->first();
        if ($role) {
            DB::table('role_permissions')->where('role_id', $role->id)->delete();
            DB::table('user_roles')->where('role_id', $role->id)->delete();
            DB::table('roles')->where('id', $role->id)->delete();
        }
        DB::table('permissions')->where('name', 'review_journals')->delete();
    }
};
