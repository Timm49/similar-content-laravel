<?php

namespace Timm49\SimilarContentLaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\ValueObjects\EmbeddingVector;

class EmbeddingVectorCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?EmbeddingVector
    {
        if ($value === null) {
            return null;
        }

        // pgvector returns something like: [0.1, 0.2, ..., 0.9]
        return new EmbeddingVector(json_decode($value, true));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof EmbeddingVector) {
            return [$key => json_encode($value->toArray())];
        }

        throw new \InvalidArgumentException('The given value is not an EmbeddingVector instance.');
    }
}