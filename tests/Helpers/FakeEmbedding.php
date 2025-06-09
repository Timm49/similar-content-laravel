<?php

namespace Timm49\SimilarContentLaravel\Tests\Helpers;

class FakeEmbedding
{
    public static function generate(): array
    {
        return array_map(
            fn () => mt_rand(0, 1000) / 1000, // values between 0.000 and 1.000
            range(1, 1536)
        );
    }
}