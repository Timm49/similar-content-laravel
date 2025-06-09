<?php

namespace Timm49\SimilarContentLaravel\Results;

readonly class SimilarContentResult
{
    public function __construct(
        public string $sourceType,
        public string $sourceId,
        public string $targetType,
        public string $targetId,
        public float  $similarityScore,
    ) {
    }
}