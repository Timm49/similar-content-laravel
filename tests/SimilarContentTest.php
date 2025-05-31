<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;
use Timm49\SimilarContentLaravel\Models\Embedding;
use Timm49\SimilarContentLaravel\SimilarContentResult;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Post;

function fake(int $dimensions = 1536): array
{
    return [0.1, 0.2, 0.3];
}

beforeEach(function () {
    Config::set('similar_content.openai_api_key', 'my-api-key');
});

it('returns empty array when getting similar content', function () {
    $article = new Article();
    
    $result = SimilarContent::getSimilarContent($article);
    
    expect($result)->toBeArray()->toBeEmpty();
});

it('stores an embedding record for the article', function () {
    $embedding = fake();
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                ['embedding' => $embedding],
            ],
        ]),
    ]);

    $article = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);

    SimilarContent::createEmbedding($article);

    $embeddingRecord = Embedding::where('embeddable_id', $article->id)->first();

    expect($embeddingRecord)->toBeInstanceOf(Embedding::class);
    expect($embeddingRecord->data)->toBe($embedding);
    expect($embeddingRecord->embeddable_type)->toBe(Article::class);
});

it('uses the correct API key', function () {
    $embedding = fake();
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                ['embedding' => $embedding],
            ],
        ]),
    ]);
    $article = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);

    SimilarContent::createEmbedding($article);

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('Authorization', 'Bearer my-api-key');
    });
});

it('uses the data from the trait method', function () {
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                ['embedding' => fake()],
            ],
        ]),
    ]);
    $article = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);

    SimilarContent::createEmbedding($article);

    Http::assertSent(function (Request $request) use ($article) {
        return $request->data()['input'] === $article->getEmbeddingData();
    });
});

it('uses default data when trait not used', function () {
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                ['embedding' => fake()],
            ],
        ]),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post',
    ]);

    SimilarContent::createEmbedding($post);

    Http::assertSent(function (Request $request) use ($post) {
        return Str::contains($request->data()['input'], $post->content);
    });
});

it('returns similar content results', function () {
    $embedding = fake();
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                ['embedding' => $embedding],
            ],
        ]),
    ]);
    $article1 = Article::create(['title' => 'Test Article 1', 'content' => 'Content 1']);
    $article2 = Article::create(['title' => 'Test Article 2', 'content' => 'Content 2']);

    Embedding::insert([
        [
            'embeddable_type' => Article::class,
            'embeddable_id' => (string)$article1->id,
            'data' => json_encode($embedding),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'embeddable_type' => Article::class,
            'embeddable_id' => (string)$article2->id,
            'data' => json_encode($embedding),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $results = SimilarContent::getSimilarContent($article1);

    expect($results)->toBeArray()
        ->toHaveCount(1)
        ->each->toBeInstanceOf(SimilarContentResult::class);

    $result = $results[0];
    expect($result->sourceType)->toBe(Article::class);
    expect($result->sourceId)->toBe((string)$article1->id);
    expect($result->targetType)->toBe(Article::class);
    expect($result->targetId)->toBe((string)$article2->id);
    expect($result->similarityScore)->toBeFloat();
});