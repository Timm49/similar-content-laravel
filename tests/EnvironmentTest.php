<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Timm49\LaravelSimilarContent\SimilarContent;

test('environment is working', function () {
    $service = app()->make(SimilarContent::class);
    expect($service)->toBeInstanceOf(SimilarContent::class);
});