<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Timm49\SimilarContentLaravel\Contracts\SimilarContentProvider;

class SimilarContentManager implements SimilarContentProvider
{
    protected SimilarContentProvider $provider;

    public function __construct()
    {
        $driver = DB::connection(config('similar_content.connection'))->getDriverName();
        $this->provider = match ($driver) {
            'pgsql' => app(PgvectorSimilarContentProvider::class),
            default => app(MysqlSimilarContentProvider::class),
        };
    }

    public function getSimilarContent(object $model): array
    {
        return $this->provider->getSimilarContent($model);
    }

    public function generateEmbeddings(Model $model): array
    {
        return $this->provider->generateEmbeddings($model);
    }

    public function generateAndStoreEmbeddings(Model $model): void
    {
        $this->provider->generateAndStoreEmbeddings($model);
    }
}