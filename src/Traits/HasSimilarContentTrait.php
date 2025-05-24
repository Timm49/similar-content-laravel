<?php

namespace Timm49\LaravelSimilarContent\Traits;

trait HasSimilarContentTrait
{
    public function getEmbeddingData(): string
    {
        return $this->toJson();
    }
}