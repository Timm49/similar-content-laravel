<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Timm49\LaravelSimilarContent\SimilarContent;
use Timm49\LaravelSimilarContent\ValueObjects\EmbedModal;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Post;
use Timm49\LaravelSimilarContent\Interfaces\EmbeddingTransformer;
use Timm49\LaravelSimilarContent\Tests\Fixtures\EmbeddingTransformers\ArticleEmbeddingTransformer;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Comment;
use Timm49\LaravelSimilarContent\DefaultEmbeddingTransformer;

test('discovers models with HasSimilarContent attribute', function () {
    $modelsPath = __DIR__ . '/Fixtures/Models';
    $models = SimilarContent::discoverModelsWithEmbeddings($modelsPath);
    
    expect($models)->toHaveCount(2);
    
    expect($models[0]->model)->toBe(Article::class);
    expect($models[0]->transformer)->toBe(ArticleEmbeddingTransformer::class);
    
    expect($models[1]->model)->toBe(Comment::class);
    expect($models[1]->transformer)->toBe(DefaultEmbeddingTransformer::class);
}); 