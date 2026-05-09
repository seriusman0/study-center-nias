<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\CabangSeeder::class);
    }

    public function test_guest_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Tamu Test',
            'email' => 'tamu@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token'])
            ->assertJsonPath('user.role.name', 'guest');
    }

    public function test_guest_can_login(): void
    {
        $role = Role::where('name', 'guest')->first();
        User::factory()->create([
            'email' => 'tamu@test.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'username' => 'tamutest',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'tamu@test.com',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $role = Role::where('name', 'guest')->first();
        User::factory()->create([
            'email' => 'tamu@test.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'username' => 'tamutest',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'tamu@test.com',
            'password' => 'wrong',
        ])->assertStatus(401);
    }

    public function test_authenticated_user_can_get_me(): void
    {
        $role = Role::where('name', 'student')->first();
        $user = User::factory()->create(['role_id' => $role->id, 'username' => 'studenttest']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('email', $user->email);
    }

    public function test_logout(): void
    {
        $user = User::factory()->create(['username' => 'logouttest']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/auth/logout')
            ->assertOk();
    }
}
