<?php

namespace Timm49\LaravelSimilarContent\Tests\Fixtures\EmbeddingTransformers;

use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Interfaces\EmbeddingTransformer;

class ArticleEmbeddingTransformer implements EmbeddingTransformer
{
    public function getEmbeddingData(Model $model): string
    {
        return $model->title . ' ' . $model->content;
    }
}