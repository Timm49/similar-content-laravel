<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentProvider;
use Timm49\SimilarContentLaravel\SimilarContentResult;

class PgvectorSimilarContentProvider implements SimilarContentProvider
{
    public function getSimilarContent(object $model): array
    {
        $source = DB::connection('pgvector')->table('embeddings')
            ->where('embeddable_type', get_class($model))
            ->where('embeddable_id', $model->id)
            ->first();

        if (!$source) return [];

        $vector = $source->data;

        $results = DB::connection('pgvector')->table('embeddings')
            ->select([
                'embeddable_type',
                'embeddable_id',
                DB::raw("1 - (data <#> '{$vector}') AS similarity_score"),
            ])
            ->where('embeddable_type', get_class($model))
            ->where('embeddable_id', '!=', $model->id)
            ->orderByDesc('similarity_score')
            ->limit(10)
            ->get();

        return $results->map(fn ($row) => new SimilarContentResult(
            sourceType: get_class($model),
            sourceId: $model->id,
            targetType: $row->embeddable_type,
            targetId: $row->embeddable_id,
            similarityScore: (float) $row->similarity_score
        ))->all();
    }

    public function generateEmbeddings(Model $model): array
    {
        // TODO: Implement generateEmbeddings() method.
    }

    public function generateAndStoreEmbeddings(Model $model): void
    {
        // TODO: Implement generateAndStoreEmbeddings() method.
    }
}
