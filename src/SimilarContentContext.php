<?php

namespace Timm49\SimilarContentLaravel;

use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Facades\SimilarContent as SimilarContentFacade;

class SimilarContentContext
{
    public function __construct(
        private Model $model,
    ) {
    }

    public function generateAndStoreEmbeddings(): void
    {
        SimilarContentFacade::generateAndStoreEmbeddings($this->model);
    }

    public function getSimilarContent(): array
    {
        return SimilarContentFacade::getSimilarContent($this->model);
    }
}   