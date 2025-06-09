<?php

namespace Timm49\SimilarContentLaravel\Traits;

trait HasSimilarContent
{
    public function getEmbeddingData(): string
    {
        return $this->toJson();
    }
}