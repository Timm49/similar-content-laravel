<?php

namespace Timm49\SimilarContentLaravel\Tests\Helpers;

use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Models\Embedding;

class FakeEmbedding
{
    public static function generate(): array
    {
        return array_map(
            fn() => mt_rand(0, 1000) / 1000, // values between 0.000 and 1.000
            range(1, 1536)
        );
    }

    public static function store(Model $model): void
    {
        Embedding::insert([
            [
                'embeddable_type' => get_class($model),
                'embeddable_id' => (string)$model->id,
                'data' => json_encode(self::generate()),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}