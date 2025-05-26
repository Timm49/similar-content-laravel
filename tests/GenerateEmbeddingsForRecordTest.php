<?php

namespace Timm49\LaravelSimilarContent\Tests\Jobs;

use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForRecord;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Services\SimilarContentService;
use Mockery;

it('creates an embedding record for the article', function () {
    // Given
    $article = Article::create([
         'title' => 'Test Article',
         'content' => 'This is a test article',
     ]);

    $mock = Mockery::mock(SimilarContentService::class);
    $mock->shouldReceive('generateAndStoreEmbeddings');
    
    // When
    (new GenerateEmbeddingsForRecord($article))->handle($mock);

    // Then
    $mock->shouldHaveReceived('generateAndStoreEmbeddings')->with($article);
});