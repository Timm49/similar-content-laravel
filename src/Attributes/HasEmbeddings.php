<?php

namespace Timm49\SimilarContentLaravel\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class HasEmbeddings
{
    public function __construct(
        public ?string $transformer = null,
    ) {
    }
} 