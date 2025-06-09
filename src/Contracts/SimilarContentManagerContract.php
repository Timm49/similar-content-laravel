<?php

namespace Timm49\SimilarContentLaravel\Contracts;

use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Results\SearchResult;
use Timm49\SimilarContentLaravel\Results\SimilarContentResult;

interface SimilarContentManagerContract
{
    public function createEmbedding(Model $model): void;

    /**
     * @return SimilarContentResult[]
     */
    public function getSimilarContent(Model $model): array;

    /**
     * @return SearchResult[]
     */
    public function search(string $query, ?array $models = []): array;
}