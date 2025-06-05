<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentDatabaseConnection;
use Timm49\SimilarContentLaravel\Models\Embedding;
use Timm49\SimilarContentLaravel\SimilarContentResult;

class SimilarContentPgVectorDatabase implements SimilarContentDatabaseConnection
{
    public function getSimilarContent(Model $model): array
    {
        $sourceEmbedding = Embedding::query()
            ->where('embeddable_type', get_class($model))
            ->where('embeddable_id', $model->id)
            ->first();

        if (!$sourceEmbedding) {
            return [];
        }

        $sqlArray = '[' . implode(',', $sourceEmbedding->data) . ']';

        // We use raw DB query here because pgvector functions like `<#>` require raw SQL
        $targetRows = DB::table('embeddings')
            ->select(
                'embeddable_type',
                'embeddable_id',
                DB::raw("1 - (data <#> '{$sqlArray}') as similarity_score")
            )
            ->where('embeddable_type', get_class($model))
            ->where('embeddable_id', '!=', $model->id)
            ->orderByDesc('similarity_score')
            ->get();

        $results = [];

        foreach ($targetRows as $row) {
            $results[] = new SimilarContentResult(
                sourceType: get_class($model),
                sourceId: $model->id,
                targetType: $row->embeddable_type,
                targetId: $row->embeddable_id,
                similarityScore: (float) $row->similarity_score
            );
        }

        return $results;
    }

    public function storeEmbedding(Model $model, array $embeddingData): void
    {
        $embedding = Embedding::firstOrNew([
            'embeddable_type' => get_class($model),
            'embeddable_id' => $model->id,
        ]);

        $embedding->data = $embeddingData;
        $embedding->updated_at = now();
        $embedding->save();
    }

    public function search(array $queryEmbedding, array $searchable): array
    {
        $sqlArray = '[' . implode(',', $queryEmbedding) . ']';

        // We use raw DB query here because pgvector functions like `<#>` require raw SQL
        $targetRows = DB::table('embeddings')
            ->select(
                'embeddable_type',
                'embeddable_id',
                DB::raw("1 - (data <#> '{$sqlArray}') as similarity_score")
            );

        if (!empty($searchable)) {
            $targetRows->whereIn('embeddable_type', $searchable);
        }

        $targetRows->orderByDesc('similarity_score');

        foreach ($targetRows->get() as $targetEmbedding) {
            $results[] = new SimilarContentResult(
                sourceType: 'query',
                sourceId: null,
                targetType: $targetEmbedding->embeddable_type,
                targetId: $targetEmbedding->embeddable_id,
                similarityScore: $targetEmbedding->similarity_score
            );
        }

        usort($results, fn($a, $b) => $b->similarityScore <=> $a->similarityScore);
        return $results;
    }
}