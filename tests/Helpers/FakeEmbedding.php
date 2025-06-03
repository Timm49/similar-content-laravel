<?php

namespace Timm49\SimilarContentLaravel\Tests\Helpers;

class FakeEmbedding
{
    public static function generate(): array
    {
        return [0.1, 0.2, 0.3];
    }
}