<?php

namespace Timm49\SimilarContentLaravel\Tests\Fixtures\Database\Factory;

use Illuminate\Database\Eloquent\Factories\Factory;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();

        return [
            'title' => $title,
            'content' => $this->faker->paragraphs(3, true),
        ];
    }
}
