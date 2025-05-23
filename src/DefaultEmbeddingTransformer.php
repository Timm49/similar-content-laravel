<?php

namespace Timm49\LaravelSimilarContent;

use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Interfaces\EmbeddingTransformer;

class DefaultEmbeddingTransformer implements EmbeddingTransformer
{
    public function getEmbeddingData(Model $model): string
    {
        return strip_tags(implode("\n", $model->except(['created_at', 'updated_at', 'id'])));
    }
}