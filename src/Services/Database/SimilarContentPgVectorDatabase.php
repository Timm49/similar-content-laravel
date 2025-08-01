<?php

namespace Timm49\SimilarContentLaravel\Services\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentDatabaseConnection;
use Timm49\SimilarContentLaravel\Models\Embedding;
use Timm49\SimilarContentLaravel\Results\SearchResult;
use Timm49\SimilarContentLaravel\Results\SimilarContentResult;

class SimilarContentPgVectorDatabase implements SimilarContentDatabaseConnection
{
    /**
     * @return SimilarContentResult[]
     */
    public function getSimilarContent(Model $model): array
    {
        $results = [];
        $limit = config('similar_content.limit_similar_results', 10);

        $sourceEmbedding = Embedding::query()
            ->where('embeddable_type', get_class($model))
            ->where('embeddable_id', $model->id)
            ->first();

        if (!$sourceEmbedding) {
            return $results;
        }

        $sqlArray = '[' . implode(',', $sourceEmbedding->data) . ']';
        $targetRows = DB::table('embeddings')
            ->select(
                'embeddable_type',
                'embeddable_id',
                DB::raw("1 - (data <=> '{$sqlArray}') as similarity_score")
            )
            ->where('embeddable_type', get_class($model))
            ->where('embeddable_id', '!=', $model->id)
            ->orderByDesc('similarity_score')
            ->limit($limit)
            ->get();

        foreach ($targetRows as $row) {
            $results[] = new SimilarContentResult(
                sourceType: get_class($model),
                sourceId: $model->id,
                targetType: $row->embeddable_type,
                targetId: $row->embeddable_id,
                similarityScore: (float) $row->similarity_score,
            );
        }

        return $results;
    }

    /**
     * @return SearchResult[]
     */
    public function search(array $queryEmbedding, array $searchable): array
    {
        $results = [];
        $limit = config('similar_content.limit_search_results', 10);
        $sqlArray = '[' . implode(',', $queryEmbedding) . ']';
        $targetRows = DB::table('embeddings')->select(
            'embeddable_type',
            'embeddable_id',
            DB::raw("1 - (data <=> '{$sqlArray}') as similarity_score")
        );

        if (!empty($searchable)) {
            $targetRows->whereIn('embeddable_type', $searchable);
        }

        $targetEmbeddings = $targetRows
            ->orderByDesc('similarity_score')
            ->limit($limit)
            ->get();

        foreach ($targetEmbeddings as $targetEmbedding) {
            $results[] = new SearchResult(
                type: $targetEmbedding->embeddable_type,
                id: $targetEmbedding->embeddable_id,
                similarityScore: $targetEmbedding->similarity_score,
            );
        }

        return $results;
    }
}