<?php

namespace Timm49\LaravelSimilarContent\Providers;

use Illuminate\Support\ServiceProvider;
use Timm49\LaravelSimilarContent\SimilarContent;
use Timm49\LaravelSimilarContent\Console\Commands\GenerateEmbeddingsCommand;

class SimilarContentProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SimilarContent::class, function ($app) {
            return new SimilarContent();
        });

        $this->mergeConfigFrom(
            __DIR__.'/../../config/similar_content.php', 'similar_content'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateEmbeddingsCommand::class,
            ]);
            $this->publishes([
                __DIR__.'/../../config/similar_content.php' => config_path('similar_content.php'),
            ], 'similar-content-configuration');
            $this->publishes([
                __DIR__.'/../../database/migrations/2023_01_01_000001_create_articles_table.php' => database_path('migrations/2023_01_01_000001_create_articles_table.php'),
                __DIR__.'/../../database/migrations/2023_01_01_000001_create_articles_table.php' => database_path('migrations/2023_01_01_000001_create_articles_table.php'),
                __DIR__.'/../../database/migrations/create_embeddings_table.php' => database_path('migrations/create_embeddings_table.php'),
            ], 'similar-content-migrations');
        }        
    }
}