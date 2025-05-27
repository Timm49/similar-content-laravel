<?php

namespace Timm49\SimilarContentLaravel\Tests\Jobs;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Timm49\SimilarContentLaravel\Services\SimilarContentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
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

it('stores an embedding record for the article', function () {
    $article = Article::create([
         'title' => 'Test Article',
         'content' => 'This is a test article',
     ]);
     
    app(SimilarContentService::class)->generateAndStoreEmbeddings($article);
    
    $embeddingRecord = DB::table('embeddings')->where('embeddable_id', $article->id)->first();

    expect($embeddingRecord)->not->toBeNull();
    expect($embeddingRecord->data)->toBeJson();
    expect($embeddingRecord->data)->toBe(json_encode([0.5, 0.6, 0.7]));
    expect($embeddingRecord->embeddable_id)->toBe($article->id);
    expect($embeddingRecord->embeddable_type)->toBe(Article::class);
});

it('it uses the correct API key', function () {
    $article = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);
    
    app(SimilarContentService::class)->generateAndStoreEmbeddings($article);

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('Authorization', 'Bearer my-api-key');
    });
});

it('it uses the data from the trait method', function () {
    $article = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);
    
    app(SimilarContentService::class)->generateAndStoreEmbeddings($article);
    
    Http::assertSent(function (Request $request) use ($article) {
        return $request->data()['input'] === $article->getEmbeddingData();
    });
});

it('it uses default data when trait not used', function () {
    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post',
    ]);
    
    app(SimilarContentService::class)->generateAndStoreEmbeddings($post);
    
    Http::assertSent(function (Request $request) use ($post) {
        return $request->data()['input'] === $post->toJson();
    });
});