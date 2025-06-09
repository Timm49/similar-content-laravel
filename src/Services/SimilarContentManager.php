<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Timm49\SimilarContentLaravel\Contracts\EmbeddingApi;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentDatabaseConnection;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentManagerContract;
use Timm49\SimilarContentLaravel\Models\Embedding;
use Timm49\SimilarContentLaravel\Results\SearchResult;
use Timm49\SimilarContentLaravel\Results\SimilarContentResult;

class SimilarContentManager implements SimilarContentManagerContract
{
    public function __construct(
        private EmbeddingApi $embeddingApi,
        private SimilarContentDatabaseConnection $connection,
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

    /**
     * @return SimilarContentResult[]
     */
    public function getSimilarContent(Model $model): array
    {
        if (!config('similar_content.cache_enabled')) {
            return $this->connection->getSimilarContent($model);
        }

        $key = "embeddings.{$model->getTable()}.{$model->id}";
        $ttl = now()->addSeconds(config('similar_content.cache_ttl', 3600));

        return Cache::remember(
            $key,
            $ttl,
            fn () => $this->connection->getSimilarContent($model)
        );
    }

    /**
     * @return SearchResult[]
     */
    public function search(string $query, ?array $models = []): array
    {
        $embedding = $this->embeddingApi->embed($query);

        return $this->connection->search($embedding, $models);
    }
}