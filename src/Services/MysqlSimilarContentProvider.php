<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentProvider;
use Timm49\SimilarContentLaravel\Jobs\GenerateAndStoreEmbeddingsJob;
use Timm49\SimilarContentLaravel\SimilarContentResult;

class MysqlSimilarContentProvider implements SimilarContentProvider
{
    public function generateEmbeddings(Model $model): array
    {
        $input = method_exists($model, 'getEmbeddingData')
            ? $model->getEmbeddingData()
            : $model->toJson();

        Log::info($input);

        $response = Http::withToken(config('similar_content.openai_api_key'))
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => 'text-embedding-3-small',
                'input' => $input,
            ])
            ->json();

        return $response['data'][0]['embedding'];
    }

    public function generateAndStoreEmbeddings(Model $model): void
    {
        $queueConnection = config('similar_content.queue_connection');

        if ($queueConnection) {
            GenerateAndStoreEmbeddingsJob::dispatch($model)->onConnection($queueConnection);
        } else {
            GenerateAndStoreEmbeddingsJob::dispatchSync($model);
        }

    }

    public function getSimilarContent(object $model): array
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
