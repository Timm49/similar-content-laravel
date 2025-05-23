<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Timm49\LaravelSimilarContent\SimilarContent;
use Timm49\LaravelSimilarContent\ValueObjects\EmbedModal;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Post;
use Timm49\LaravelSimilarContent\EmbeddingTransformer;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Comment;

test('discovers models with HasSimilarContent attribute', function () {
    $modelsPath = __DIR__ . '/Fixtures/Models';
    $models = SimilarContent::discoverModelsWithEmbeddings($modelsPath);
    dump($models[0]);
    expect($models)->toHaveCount(2);
    expect($models[0]->model)->toBe(Article::class);
    expect($models[0]->transformer)->toBe('text-embedding-3-small');
    expect($models)->toHaveCount(2);
    expect($models[1]->model)->toBe(Comment::class);
    expect($models[1]->transformer)->toBe(EmbeddingTransformer::class);
}); 