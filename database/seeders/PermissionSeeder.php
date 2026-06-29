<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'manage_users',     'description' => 'Kelola pengguna'],
            ['name' => 'manage_roles',     'description' => 'Kelola role & permission'],
            ['name' => 'manage_cabangs',   'description' => 'Kelola cabang'],
            ['name' => 'manage_blogs',     'description' => 'Kelola semua blog'],
            ['name' => 'create_blog',      'description' => 'Tulis blog'],
            ['name' => 'view_blogs',       'description' => 'Lihat blog'],
            ['name' => 'create_schedule',  'description' => 'Buat jadwal mentor'],
            ['name' => 'approve_payment',  'description' => 'Setujui pembayaran'],
        ];

        foreach ($permissions as $p) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $p['name']],
                ['name' => $p['name'], 'description' => $p['description'], 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $rolePermMap = [
            'admin'     => ['manage_users', 'manage_roles', 'manage_cabangs', 'manage_blogs', 'create_blog', 'view_blogs', 'approve_payment'],
            'fulltimer' => ['manage_blogs', 'create_blog', 'view_blogs'],
            'mentor'    => ['create_blog', 'view_blogs', 'create_schedule'],
            'student'   => ['create_blog', 'view_blogs'],
            'guest'     => ['view_blogs'],
        ];

        foreach ($rolePermMap as $roleName => $perms) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            if (! $roleId) {
                continue;
            }
            $permIds = DB::table('permissions')->whereIn('name', $perms)->pluck('id');
            $rows = $permIds->map(fn($pid) => ['role_id' => $roleId, 'permission_id' => $pid])->all();
            DB::table('role_permissions')->where('role_id', $roleId)->delete();
            if ($rows) {
                DB::table('role_permissions')->insert($rows);
            }
        }
    }
}
