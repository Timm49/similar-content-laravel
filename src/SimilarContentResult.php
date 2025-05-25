<?php

namespace Timm49\LaravelSimilarContent;

class SimilarContentResult
{
    public function __construct(
        public readonly string $sourceType,
        public readonly string $sourceId,
        public readonly string $targetType,
        public readonly string $targetId,
        public readonly float $similarityScore,
    ) {
    }
} 