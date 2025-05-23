<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Timm49\LaravelSimilarContent\SimilarContent;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Post;

test('discovers models with HasSimilarContent attribute', function () {
    $modelsPath = __DIR__ . '/Fixtures/Models';
    $models = SimilarContent::discoverModelsWithEmbeddings($modelsPath);
    expect($models)
        ->toContain(Article::class)
        ->not()->toContain(Post::class)
        ->toHaveCount(1);
}); 