<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Timm49\SimilarContentLaravel\Contracts\EmbeddingApi;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentManagerContract;
use Timm49\SimilarContentLaravel\Models\Embedding;
use Timm49\SimilarContentLaravel\SimilarContentResult;

class SimilarContentManager implements SimilarContentManagerContract
{
    public function __construct(
        private EmbeddingApi $embeddingApi,
    ) {
    }

    public function createEmbedding(Model $model): void
    {
        $embedding = Embedding::firstOrNew([
            'embeddable_type' => get_class($model),
            'embeddable_id' => $model->id,
        ]);

        $embedding->data = $this->embeddingApi->embedModel($model);
        $embedding->updated_at = now();
        $embedding->save();
    }

    public function getSimilarContent(Model $model): array
    {
        if (!config('similar_content.cache_enabled')) {
            return $this->querySimilarContent($model);
        }

        $store = config('similar_content.cache_store');
        $ttl = now()->addSeconds(config('similar_content.cache_ttl', 3600));

        $cache = $store ? Cache::store($store) : Cache::store();
        $key = "embeddings.{$model->getTable()}.{$model->id}";
        return $cache->remember(
            $key,
            $ttl,
            fn () => $this->querySimilarContent($model)
        );
    }

    public function search(string $query, ?array $searchable = []): array
    {
        $q = Embedding::query();

        if (count($searchable) > 0) {
            $q->whereIn('embeddable_type', $searchable);
        }

        $results = [];
        foreach ($q->get() as $targetEmbedding) {
            $targetVector = $targetEmbedding->data;
            $similarityScore = $this->calculateCosineSimilarity($this->embeddingApi->embed($query), $targetVector);

            $results[] = new SimilarContentResult(
                sourceType: 'query',
                sourceId: null,
                targetType: $targetEmbedding->embeddable_type,
                targetId: $targetEmbedding->embeddable_id,
                similarityScore: $similarityScore
            );
        }

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

    private function querySimilarContent(Model $model): array
    {
        $sourceEmbedding = Embedding::where('embeddable_type', get_class($model))
            ->where('embeddable_id', $model->id)
            ->first();

        if (!$sourceEmbedding) {
            return [];
        }

        $sourceVector = $sourceEmbedding->data;
        $results = [];

        $targetEmbeddings = Embedding::where('embeddable_type', get_class($model))
            ->where('embeddable_id', '!=', $model->id)
            ->get();

        foreach ($targetEmbeddings as $targetEmbedding) {
            $targetVector = $targetEmbedding->data;
            $similarityScore = $this->calculateCosineSimilarity($sourceVector, $targetVector);

            $results[] = new SimilarContentResult(
                sourceType: get_class($model),
                sourceId: $model->id,
                targetType: $targetEmbedding->embeddable_type,
                targetId: $targetEmbedding->embeddable_id,
                similarityScore: $similarityScore
            );
        }

        usort($results, fn($a, $b) => $b->similarityScore <=> $a->similarityScore);

        return $results;
    }
}