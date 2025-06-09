<?php

namespace Timm49\SimilarContentLaravel\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SimilarContentDatabaseConnection
{
    public function getSimilarContent(Model $model): array;

    public function search(array $queryEmbedding, array $searchable): array;
}