<?php

namespace Timm49\SimilarContentLaravel\Contracts;

use Illuminate\Database\Eloquent\Model;

interface EmbeddingApi
{
    public function generateEmbedding(Model $model): array;
}