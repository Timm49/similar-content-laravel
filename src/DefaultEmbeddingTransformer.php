<?php

namespace Timm49\LaravelSimilarContent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Timm49\LaravelSimilarContent\Interfaces\EmbeddingTransformer;

class DefaultEmbeddingTransformer implements EmbeddingTransformer
{
    public function getEmbeddingData(Model $model): string
    {
        return $model->toJson();
    }
}