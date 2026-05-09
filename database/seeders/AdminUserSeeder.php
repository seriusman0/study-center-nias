<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

        if (! $adminRoleId) {
            $this->command->error('Role admin tidak ditemukan. Jalankan RoleSeeder terlebih dahulu.');
            return;
        }

        $now = now();

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@studycenter.com'],
            [
                'name'              => 'Administrator',
                'username'          => 'administrator',
                'email'             => 'admin@studycenter.com',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'profile_public'    => false,
                'cv_enabled'        => false,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        );

        $userId = DB::table('users')->where('email', 'admin@studycenter.com')->value('id');

        if ($userId) {
            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $userId, 'role_id' => $adminRoleId],
                ['user_id' => $userId, 'role_id' => $adminRoleId, 'created_at' => $now, 'updated_at' => $now]
            );

            DB::table('admin_profiles')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'user_id'         => $userId,
                    'employee_number' => 'ADM-001',
                    'department'      => 'Manajemen',
                    'position'        => 'Administrator',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]
            );
        }

        $this->command->info('Admin user admin@studycenter.com berhasil dibuat/diperbarui.');
    }
}
