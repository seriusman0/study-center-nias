<?php

namespace Tests\Feature;

use App\Models\CvData;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\CabangSeeder::class);
        $this->user = User::factory()->create([
            'role_id' => Role::where('name', 'student')->value('id'),
            'profile_public' => true,
        ]);
    }

    public function test_public_profile_is_accessible(): void
    {
        $this->getJson("/api/profil/{$this->user->username}")
            ->assertOk()
            ->assertJsonPath('user.username', $this->user->username);
    }

    public function test_private_profile_returns_404(): void
    {
        $this->user->update(['profile_public' => false]);

        $this->getJson("/api/profil/{$this->user->username}")
            ->assertStatus(404);
    }

    public function test_user_can_update_profile(): void
    {
        $token = $this->user->createToken('t')->plainTextToken;

        $this->withToken($token)->putJson('/api/profile', [
            'bio' => 'Bio baru saya.',
            'profile_public' => true,
        ])->assertOk()->assertJsonPath('bio', 'Bio baru saya.');
    }

    public function test_cv_data_save_and_retrieve(): void
    {
        $token = $this->user->createToken('t')->plainTextToken;

        $this->withToken($token)->putJson('/api/cv', [
            'pendidikan' => [['jenjang' => 'S1', 'institusi' => 'Universitas Nias', 'tahun_lulus' => '2023']],
            'keterampilan' => ['PHP', 'React'],
            'template' => 'template1',
        ])->assertOk();

        $this->withToken($token)->getJson('/api/cv')
            ->assertOk()
            ->assertJsonPath('keterampilan.0', 'PHP');
    }
}
