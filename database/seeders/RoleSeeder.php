<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrator'],
            ['name' => 'fulltimer', 'description' => 'Full Timer'],
            ['name' => 'mentor', 'description' => 'Mentor'],
            ['name' => 'student', 'description' => 'Student'],
            ['name' => 'guest', 'description' => 'Tamu Publik'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['name' => $role['name']], $role);
        }
    }
}
