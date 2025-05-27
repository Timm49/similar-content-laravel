<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Illuminate\Support\Facades\DB;
use Timm49\SimilarContentLaravel\SimilarContentContext;
use Timm49\SimilarContentLaravel\SimilarContentResult;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

it('returns similar content results', function () {

    $article1 = Article::create(['title' => 'Test Article 1', 'content' => 'Content 1']);
    $article2 = Article::create(['title' => 'Test Article 2', 'content' => 'Content 2']);
    
    DB::table('embeddings')->insert([
        [
            'embeddable_type' => Article::class,
            'embeddable_id' => (string)$article1->id,
            'data' => json_encode([0.1, 0.2, 0.3]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'embeddable_type' => Article::class,
            'embeddable_id' => (string)$article2->id,
            'data' => json_encode([0.1, 0.2, 0.4]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $model = new SimilarContentContext($article1);
    $results = $model->getSimilarContent();

    expect($results)->toBeArray()
        ->toHaveCount(1)
        ->each->toBeInstanceOf(SimilarContentResult::class);

    $result = $results[0];
    expect($result->sourceType)->toBe(Article::class);
    expect($result->sourceId)->toBe((string)$article1->id);
    expect($result->targetType)->toBe(Article::class);
    expect($result->targetId)->toBe((string)$article2->id);
    expect($result->similarityScore)->toBeFloat();
})->uses(RefreshDatabase::class); 