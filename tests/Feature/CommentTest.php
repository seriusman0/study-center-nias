<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Cabang;
use App\Models\Comment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $guest;
    private Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\CabangSeeder::class);

        $this->student = User::factory()->create(['role_id' => Role::where('name', 'student')->value('id')]);
        $this->guest = User::factory()->create(['role_id' => Role::where('name', 'guest')->value('id')]);
        $cabang = Cabang::first();
        $this->blog = Blog::factory()->create([
            'user_id' => $this->student->id,
            'cabang_id' => $cabang->id,
            'published_at' => now(),
        ]);
    }

    public function test_public_can_read_comments(): void
    {
        Comment::create(['blog_id' => $this->blog->id, 'user_id' => $this->guest->id, 'content' => 'Komentar pertama']);

        $this->getJson("/api/blogs/{$this->blog->id}/comments")
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_authenticated_user_can_comment(): void
    {
        $token = $this->guest->createToken('t')->plainTextToken;

        $this->withToken($token)->postJson("/api/blogs/{$this->blog->id}/comments", [
            'content' => 'Komentar dari tamu.',
        ])->assertStatus(201)->assertJsonPath('content', 'Komentar dari tamu.');
    }

    public function test_unauthenticated_cannot_comment(): void
    {
        $this->postJson("/api/blogs/{$this->blog->id}/comments", [
            'content' => 'Spam.',
        ])->assertStatus(401);
    }

    public function test_threaded_reply(): void
    {
        $token = $this->guest->createToken('t')->plainTextToken;
        $parent = Comment::create(['blog_id' => $this->blog->id, 'user_id' => $this->guest->id, 'content' => 'Parent']);

        $this->withToken($token)->postJson("/api/blogs/{$this->blog->id}/comments", [
            'content' => 'Reply',
            'parent_id' => $parent->id,
        ])->assertStatus(201)->assertJsonPath('parent_id', $parent->id);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $token = $this->guest->createToken('t')->plainTextToken;
        $comment = Comment::create(['blog_id' => $this->blog->id, 'user_id' => $this->guest->id, 'content' => 'Test']);

        $this->withToken($token)->deleteJson("/api/comments/{$comment->id}")
            ->assertOk();

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }
}
