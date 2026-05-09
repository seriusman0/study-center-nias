<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);
        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'content' => fake()->paragraphs(3, true),
            'image' => null,
            'published_at' => null,
        ];
    }
}
