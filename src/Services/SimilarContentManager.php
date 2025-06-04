<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Timm49\SimilarContentLaravel\Contracts\EmbeddingApi;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentDatabaseConnection;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentManagerContract;

class SimilarContentManager implements SimilarContentManagerContract
{
    public function __construct(
        private EmbeddingApi $embeddingApi,
        private SimilarContentDatabaseConnection $connection,
    ) {
    }

    public function createEmbedding(Model $model): void
    {
        $this->connection->storeEmbedding($model, $this->embeddingApi->embedModel($model));
    }

    public function getSimilarContent(Model $model): array
    {
        if (!config('similar_content.cache_enabled')) {
            return $this->connection->getSimilarContent($model);
        }

        $store = config('similar_content.cache_store');
        $ttl = now()->addSeconds(config('similar_content.cache_ttl', 3600));
        $cache = $store ? Cache::store($store) : Cache::store();
        $key = "embeddings.{$model->getTable()}.{$model->id}";
        return $cache->remember(
            $key,
            $ttl,
            fn () => $this->connection->getSimilarContent($model)
        );
    }

    public function search(string $query, ?array $searchable = []): array
    {
        return $this->connection->search($this->embeddingApi->embed($query), $searchable);
    }
}