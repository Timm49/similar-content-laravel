<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Illuminate\Support\Facades\Http;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Timm49\SimilarContentLaravel\Tests\Helpers\FakeEmbedding;

uses(AutoGenerateEnabledTestCase::class);

it('uses model observer to auto generate embeddings when enabled in config', function () {
    $embedding = FakeEmbedding::generate();
    
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                [
                    'index' => 0,
                    'embedding' => $embedding
                ],
            ],
        ]),
    ]);

    $this->assertDatabaseCount('embeddings', 0);

    $article = Article::create([
        'title' => 'My title',
        'content' => 'My content'
    ]);

    $this->assertDatabaseCount('embeddings', 1);
    $this->assertDatabaseHas('embeddings', [
        'embeddable_id' => $article->id,
        'embeddable_type' => Article::class,
        'data' => json_encode($embedding),
    ]);
});