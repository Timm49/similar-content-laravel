<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;
use Timm49\SimilarContentLaravel\Models\Embedding;
use Timm49\SimilarContentLaravel\SimilarContentResult;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Database\Factory\ArticleFactory;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Database\Factory\CommentFactory;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Timm49\SimilarContentLaravel\Tests\Helpers\FakeEmbedding;

beforeEach(function () {
    Config::set('similar_content.openai_api_key', 'my-api-key');

    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                [
                    'index' => 0,
                    'embedding' => FakeEmbedding::generate()
                ],
            ],
        ]),
    ]);
});

it('can search for similar content using a search query', function () {
    $articleOne = ArticleFactory::new()->create();
    $articleTwo = ArticleFactory::new()->create();
    $comment = CommentFactory::new()->create();

    // Create embedding for the articles which should be in the result
    Embedding::create([
        'embeddable_type' => get_class($articleOne),
        'embeddable_id' => $articleOne->id,
        'data' => FakeEmbedding::generate(),
    ]);

    Embedding::create([
        'embeddable_type' => get_class($articleTwo),
        'embeddable_id' => $articleTwo->id,
        'data' => [0.9, 0.1, 0.9],
    ]);

    // Create embedding for the comment which should NOT be in the result
    Embedding::create([
        'embeddable_type' => get_class($comment),
        'embeddable_id' => $comment->id,
        'data' => FakeEmbedding::generate(),
    ]);

    $searchQuery = 'blue sweater';
    $searchable = [Article::class];

    $response = SimilarContent::search($searchQuery, $searchable);

    $this->assertCount(2, $response);
    $this->assertInstanceOf(SimilarContentResult::class, $response[0]);

    $this->assertSame('query', $response[0]->sourceType);
    $this->assertSame(1.0, $response[0]->similarityScore);
    $this->assertSame((string)$articleOne->id, $response[0]->targetId);
    $this->assertSame(Article::class, $response[0]->targetType);
    $this->assertNull($response[0]->sourceId);

    $this->assertSame('query', $response[1]->sourceType);
    $this->assertNotSame(1.0, $response[1]->similarityScore);
    $this->assertSame((string)$articleTwo->id, $response[1]->targetId);
    $this->assertSame(Article::class, $response[1]->targetType);
    $this->assertNull($response[1]->sourceId);
});


it('searches all embeddings when not specified', function () {
    $articleOne = ArticleFactory::new()->create();
    $articleTwo = ArticleFactory::new()->create();
    $comment = CommentFactory::new()->create();

    // Create embeddings which should all be in the result
    Embedding::create([
        'embeddable_type' => get_class($articleOne),
        'embeddable_id' => $articleOne->id,
        'data' => FakeEmbedding::generate(),
    ]);

    Embedding::create([
        'embeddable_type' => get_class($articleTwo),
        'embeddable_id' => $articleTwo->id,
        'data' => [0.9, 0.1, 0.9],
    ]);

    Embedding::create([
        'embeddable_type' => get_class($comment),
        'embeddable_id' => $comment->id,
        'data' => [0.9, 0.1, 0.9],
    ]);

    $response = SimilarContent::search('blue sweater');

    $this->assertCount(3, $response);
});
