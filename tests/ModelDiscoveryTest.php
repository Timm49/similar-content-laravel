<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Timm49\LaravelSimilarContent\SimilarContent;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Comment;

test('discovers models with HasSimilarContent attribute', function () {
    $modelsPath = __DIR__ . '/Fixtures/Models';
    $models = SimilarContent::discoverModelsWithEmbeddings($modelsPath);
    
    expect($models)->toHaveCount(2);
    expect($models[0])->toBe(Article::class);
    expect($models[1])->toBe(Comment::class);
}); 