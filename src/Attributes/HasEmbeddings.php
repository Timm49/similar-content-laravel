<?php

namespace Timm49\LaravelSimilarContent\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class HasSimilarContent
{
    public function __construct(
        public string $transformer,
    ) {
    }
} 