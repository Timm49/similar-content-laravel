<?php

namespace Timm49\LaravelSimilarContent\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface EmbeddingTransformer
{
    public function getEmbeddingData(Model $model): string;
} 