<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Timm49\SimilarContentLaravel\Contracts\EmbeddingApi;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentDatabaseConnection;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentManagerContract;
use Timm49\SimilarContentLaravel\Models\Embedding;

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