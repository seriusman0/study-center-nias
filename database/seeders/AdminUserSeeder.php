<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = DB::table('roles')->where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('Role admin tidak ditemukan. Jalankan RoleSeeder terlebih dahulu.');
            return;
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@studycenter.com'],
            [
                'name'              => 'Administrator',
                'username'          => 'administrator',
                'email'             => 'admin@studycenter.com',
                'password'          => Hash::make('password'),
                'role_id'           => $adminRole->id,
                'is_active'         => true,
                'profile_public'    => false,
                'cv_enabled'        => false,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        $this->command->info('Admin user admin@studycenter.com berhasil dibuat/diperbarui.');
    }
}
