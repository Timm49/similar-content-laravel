<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Illuminate\Support\Facades\Http;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;
use Timm49\SimilarContentLaravel\Models\Embedding;
use Timm49\SimilarContentLaravel\Results\SearchResult;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Database\Factory\ArticleFactory;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Database\Factory\CommentFactory;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Timm49\SimilarContentLaravel\Tests\Helpers\FakeEmbedding;

beforeEach(function () {
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
        'data' => FakeEmbedding::generate(),
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
    $this->assertInstanceOf(SearchResult::class, $response[0]);

    $this->assertIsFloat($response[0]->similarityScore);
    $this->assertSame(Article::class, $response[0]->type);

    $this->assertNotSame(1.0, $response[1]->similarityScore);
    $this->assertSame(Article::class, $response[1]->type);
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
        'data' => FakeEmbedding::generate(),
    ]);

    Embedding::create([
        'embeddable_type' => get_class($comment),
        'embeddable_id' => $comment->id,
        'data' => FakeEmbedding::generate(),
    ]);

    $response = SimilarContent::search('blue sweater');

    $this->assertCount(3, $response);
});
