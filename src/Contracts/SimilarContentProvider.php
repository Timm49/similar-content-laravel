<?php

namespace Timm49\SimilarContentLaravel\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SimilarContentProvider
{
    public function getSimilarContent(object $model): array;
    public function generateEmbeddings(Model $model): array;
    public function generateAndStoreEmbeddings(Model $model): void;
}