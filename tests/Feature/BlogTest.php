<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Cabang;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $guest;
    private Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\CabangSeeder::class);

        $studentRole = Role::where('name', 'student')->first();
        $guestRole = Role::where('name', 'guest')->first();
        $this->cabang = Cabang::first();
        $this->student = User::factory()->create(['role_id' => $studentRole->id]);
        $this->guest = User::factory()->create(['role_id' => $guestRole->id]);
    }

    public function test_public_can_list_blogs(): void
    {
        Blog::factory()->count(3)->create([
            'user_id' => $this->student->id,
            'cabang_id' => $this->cabang->id,
            'published_at' => now(),
        ]);

        $this->getJson('/api/blogs')->assertOk()->assertJsonPath('total', 3);
    }

    public function test_student_can_create_blog(): void
    {
        $token = $this->student->createToken('t')->plainTextToken;

        $this->withToken($token)->postJson('/api/blogs', [
            'title' => 'Blog Pertama',
            'content' => 'Isi konten blog pertama yang cukup panjang.',
            'cabang_id' => $this->cabang->id,
            'tags' => ['Pendidikan', 'Nias'],
        ])->assertStatus(201)->assertJsonPath('title', 'Blog Pertama');
    }

    public function test_guest_cannot_create_blog(): void
    {
        $token = $this->guest->createToken('t')->plainTextToken;

        $this->withToken($token)->postJson('/api/blogs', [
            'title' => 'Blog Tamu',
            'content' => 'Isi konten.',
            'cabang_id' => $this->cabang->id,
        ])->assertStatus(403);
    }

    public function test_author_can_update_own_blog(): void
    {
        $token = $this->student->createToken('t')->plainTextToken;
        $blog = Blog::factory()->create([
            'user_id' => $this->student->id,
            'cabang_id' => $this->cabang->id,
            'published_at' => now(),
        ]);

        $this->withToken($token)->putJson("/api/blogs/{$blog->id}", [
            'title' => 'Judul Baru',
        ])->assertOk()->assertJsonPath('title', 'Judul Baru');
    }

    public function test_other_user_cannot_update_blog(): void
    {
        $other = User::factory()->create(['role_id' => $this->student->role_id]);
        $token = $other->createToken('t')->plainTextToken;
        $blog = Blog::factory()->create([
            'user_id' => $this->student->id,
            'cabang_id' => $this->cabang->id,
            'published_at' => now(),
        ]);

        $this->withToken($token)->putJson("/api/blogs/{$blog->id}", [
            'title' => 'Hack',
        ])->assertStatus(403);
    }

    public function test_filter_by_cabang(): void
    {
        $cabang2 = Cabang::skip(1)->first();
        Blog::factory()->create(['user_id' => $this->student->id, 'cabang_id' => $this->cabang->id, 'published_at' => now()]);
        Blog::factory()->create(['user_id' => $this->student->id, 'cabang_id' => $cabang2->id, 'published_at' => now()]);

        $this->getJson('/api/blogs?cabang=' . $this->cabang->slug)
            ->assertOk()
            ->assertJsonPath('total', 1);
    }
}
