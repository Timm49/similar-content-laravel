<?php

namespace Timm49\LaravelSimilarContent\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class HasEmbeddings
{
    public function __construct(
        public ?string $column = null,
        public ?string $model = null,
    ) {
    }
} 