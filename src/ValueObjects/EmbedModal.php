<?php

namespace Timm49\LaravelSimilarContent\ValueObjects;

class EmbedModal
{
    public function __construct(
        public string $model,
        public string $transformer,
    ) {}
}