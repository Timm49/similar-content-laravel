<?php

namespace Timm49\SimilarContentLaravel\Tests\Fixtures\Database\Factory;

use Illuminate\Database\Eloquent\Factories\Factory;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Comment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();

        return [
            'content' => $this->faker->paragraphs(3, true),
        ];
    }
}
