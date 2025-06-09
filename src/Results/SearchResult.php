<?php

namespace Timm49\SimilarContentLaravel\Results;

readonly class SearchResult
{
    public function __construct(
        public string $type,
        public string $id,
        public float  $similarityScore,
    ) {
    }
}