<?php

namespace Timm49\SimilarContentLaravel;

class SimilarContentResult
{
    public function __construct(
        public readonly string $sourceType,
        public readonly ?string $sourceId = null,
        public readonly string $targetType,
        public readonly string $targetId,
        public readonly float $similarityScore,
    ) {
    }
} 