<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;
use Timm49\SimilarContentLaravel\SimilarContentResult;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Post;

beforeEach(function () {

    Config::set('similar_content.openai_api_key', 'my-api-key');

    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                ['embedding' => [0.5, 0.6, 0.7]],
            ],
        ]),
    ]);
});


it('returns empty array when getting similar content', function () {
    $article = new Article();
    
    $result = SimilarContent::getSimilarContent($article);
    
    expect($result)->toBeArray()->toBeEmpty();
});


it('generates and stores an embedding for a model', function () {
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                ['embedding' => [0.5, 0.6, 0.7]],
            ],
        ]),
    ]);

    $article = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article about AI.',
    ]);

    SimilarContent::createEmbedding($article);

    $embedding = DB::table('embeddings')
        ->where('embeddable_type', Article::class)
        ->where('embeddable_id', $article->id)
        ->first();

    expect(json_decode($embedding->data))->toEqual([0.5, 0.6, 0.7]);
});


it('stores an embedding record for the article', function () {
    $article = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);

    SimilarContent::createEmbedding($article);

    $embeddingRecord = DB::table('embeddings')->where('embeddable_id', $article->id)->first();

    expect($embeddingRecord)->not->toBeNull();
    expect($embeddingRecord->data)->toBeJson();
    expect($embeddingRecord->data)->toBe(json_encode([0.5, 0.6, 0.7]));
    expect($embeddingRecord->embeddable_id)->toBe($article->id);
    expect($embeddingRecord->embeddable_type)->toBe(Article::class);
});

it('uses the correct API key', function () {
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