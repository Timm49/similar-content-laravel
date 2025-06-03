<?php

namespace Timm49\SimilarContentLaravel\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SimilarContentManagerContract
{
    public function createEmbedding(Model $model): void;
    public function getSimilarContent(Model $model): array;
    public function search(string $query, ?array $searchable = []): array;
}