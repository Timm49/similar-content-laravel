<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Timm49\LaravelSimilarContent\SimilarContent;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;

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