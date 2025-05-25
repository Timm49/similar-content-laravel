<?php

namespace Timm49\LaravelSimilarContent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SimilarContentContext
{
    private Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getSimilarContent(): array
    {
        $sourceEmbedding = DB::table('embeddings')
            ->where('embeddable_type', get_class($this->model))
            ->where('embeddable_id', $this->model->id)
            ->first();

        if (!$sourceEmbedding) {
            return [];
        }

        $sourceVector = json_decode($sourceEmbedding->data, true);
        $results = [];

        $targetEmbeddings = DB::table('embeddings')
            ->where('embeddable_type', get_class($this->model))
            ->where('embeddable_id', '!=', $this->model->id)
            ->get();

        foreach ($targetEmbeddings as $targetEmbedding) {
            $targetVector = json_decode($targetEmbedding->data, true);
            $similarityScore = $this->calculateCosineSimilarity($sourceVector, $targetVector);

            $results[] = new SimilarContentResult(
                sourceType: get_class($this->model),
                sourceId: $this->model->id,
                targetType: $targetEmbedding->embeddable_type,
                targetId: $targetEmbedding->embeddable_id,
                similarityScore: $similarityScore
            );
        }

        // Sort results by similarity score in descending order
        usort($results, fn($a, $b) => $b->similarityScore <=> $a->similarityScore);

        return $results;
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