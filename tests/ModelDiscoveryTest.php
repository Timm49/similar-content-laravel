<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Timm49\SimilarContentLaravel\Facades\SimilarContent;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Comment;

test('discovers models with HasEmbeddings attribute', function () {
    $modelsPath = __DIR__ . '/Fixtures/Models';
    $models = SimilarContent::getRegisteredModels($modelsPath);
    
    expect($models)->toHaveCount(2);
    expect($models[0])->toBe(Article::class);
    expect($models[1])->toBe(Comment::class);
}); 