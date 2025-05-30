<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Timm49\SimilarContentLaravel\Attributes\HasEmbeddings;
use Timm49\SimilarContentLaravel\Contracts\EmbeddingApi;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentManagerContract;
use Timm49\SimilarContentLaravel\SimilarContentContext;
use Timm49\SimilarContentLaravel\SimilarContentResult;

class SimilarContentManager implements SimilarContentManagerContract
{
    private static array $registeredModels = [];

    public function __construct(
        private EmbeddingApi $embeddingApi
    ) {
    }

    public function createEmbedding(Model $model): void
    {
        DB::table('embeddings')->updateOrInsert(
            [
                'embeddable_type' => get_class($model),
                'embeddable_id' => $model->id,
            ],
            [
                'data' => json_encode($this->embeddingApi->generateEmbeddings($model)),
                'updated_at' => now(),
            ]
        );
    }


    public static function getRegisteredModels(?string $path = null): array
    {
        self::$registeredModels = [];
        $path ??= config('similar_content.models_path', app_path('Models'));

        foreach (glob($path . '/*.php') as $file) {
            $className = self::extractNamespaceFromFile($file) . '\\' . basename($file, '.php');

            if (! class_exists($className)) {
                continue;
            }

            if (! is_subclass_of($className, Model::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);
            $attributes = $reflection->getAttributes(HasEmbeddings::class);

            if (! empty($attributes)) {
                self::$registeredModels[] = $className;
            }
        }

        return self::$registeredModels;
    }

    static function extractNamespaceFromFile($filePath): ?string {
        $fileContents = file_get_contents($filePath);
        $namespacePattern = '/namespace\s+([^;]+);/';

        if (preg_match($namespacePattern, $fileContents, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function getSimilarContent(Model $model): array
    {
        $sourceEmbedding = DB::table('embeddings')
            ->where('embeddable_type', get_class($model))
            ->where('embeddable_id', $model->id)
            ->first();

        if (!$sourceEmbedding) {
            return [];
        }

        $sourceVector = json_decode($sourceEmbedding->data, true);
        $results = [];

        $targetEmbeddings = DB::table('embeddings')
            ->where('embeddable_type', get_class($model))
            ->where('embeddable_id', '!=', $model->id)
            ->get();

        foreach ($targetEmbeddings as $targetEmbedding) {
            $targetVector = json_decode($targetEmbedding->data, true);
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