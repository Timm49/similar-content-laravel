<?php

namespace Timm49\SimilarContentLaravel\Observers;

use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;

class ModelObserver
{
    public function saved(Model $model)
    {
        SimilarContent::createEmbedding($model);
    }
}