<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Timm49\SimilarContentLaravel\SimilarContent;

test('environment is working', function () {
    $service = app()->make(SimilarContent::class);
    expect($service)->toBeInstanceOf(SimilarContent::class);
});