<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentDatabaseConnection;
use Timm49\SimilarContentLaravel\Models\Embedding;
use Timm49\SimilarContentLaravel\Results\SearchResult;
use Timm49\SimilarContentLaravel\Results\SimilarContentResult;

class SimilarContentDefaultDatabase implements SimilarContentDatabaseConnection
{
    /**
     * @return SimilarContentResult[]
     */
    public function getSimilarContent(Model $model): array
    {
        $results = [];

        $sourceEmbedding = Embedding::where('embeddable_type', get_class($model))
            ->where('embeddable_id', $model->id)
            ->first();

        if (!$sourceEmbedding) {
            return $results;
        }

        $targetEmbeddings = Embedding::where('embeddable_type', get_class($model))
            ->where('embeddable_id', '!=', $model->id)
            ->get();

        foreach ($targetEmbeddings as $targetEmbedding) {
            $results[] = new SimilarContentResult(
                sourceType: get_class($model),
                sourceId: $model->id,
                targetType: $targetEmbedding->embeddable_type,
                targetId: $targetEmbedding->embeddable_id,
                similarityScore: $this->calculateCosineSimilarity($sourceEmbedding->data, $targetEmbedding->data),
            );
        }

        return collect($results)->sortByDesc('similarityScore')->values()->all();
    }

    /**
     * @return SearchResult[]
     */
    public function search(array $queryEmbedding, array $searchable): array
    {
        $results = [];
        $q = Embedding::query();

        if (count($searchable) > 0) {
            $q->whereIn('embeddable_type', $searchable);
        }

        foreach ($q->get() as $targetEmbedding) {
            $results[] = new SearchResult(
                type: $targetEmbedding->embeddable_type,
                id: $targetEmbedding->embeddable_id,
                similarityScore: $this->calculateCosineSimilarity($queryEmbedding, $targetEmbedding->data),
            );
        }

        return collect($results)->sortByDesc('similarityScore')->values()->all();
    }

    private function calculateCosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $magnitudeA += $vectorA[$i] * $vectorA[$i];
            $magnitudeB += $vectorB[$i] * $vectorB[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA === 0 || $magnitudeB === 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
}