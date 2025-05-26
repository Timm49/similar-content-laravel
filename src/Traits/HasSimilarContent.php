<?php

namespace Timm49\LaravelSimilarContent\Traits;

trait HasSimilarContent
{
    public function getEmbeddingData(): string
    {
        return $this->toJson();
    }
}