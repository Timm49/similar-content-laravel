<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Timm49\LaravelSimilarContent\SimilarContent;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

it('returns a SimilarContentContext instance when calling for()', function () {
    $article = new Article();
    
    $result = SimilarContent::for($article);
    
    expect($result)->toBeInstanceOf(\Timm49\LaravelSimilarContent\SimilarContentContext::class);
});

it('returns empty array when getting similar content', function () {
    $article = new Article();
    
    $result = SimilarContent::for($article)->getSimilarContent();
    
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

    SimilarContent::for($article)->generateAndStoreEmbeddings();

    $embedding = DB::table('embeddings')
        ->where('embeddable_type', Article::class)
        ->where('embeddable_id', $article->id)
        ->first();

    expect(json_decode($embedding->data))->toEqual([0.5, 0.6, 0.7]);
});
