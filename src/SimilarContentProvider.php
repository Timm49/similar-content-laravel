<?php

namespace Timm49\SimilarContentLaravel;

use Illuminate\Support\ServiceProvider;
use Timm49\SimilarContentLaravel\Console\Commands\GenerateEmbeddingsCommand;
use Timm49\SimilarContentLaravel\Console\Commands\InstallSimilarContentCommand;
use Timm49\SimilarContentLaravel\Services\OpenAIEmbeddingApi;
use Timm49\SimilarContentLaravel\Services\SimilarContentManager;

class SimilarContentProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/similar_content.php', 'similar_content'
        );
        $this->app->singleton('similar-content', fn () => new SimilarContentManager(
            new OpenAIEmbeddingApi()
        ));
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->commands([
                GenerateEmbeddingsCommand::class,
                InstallSimilarContentCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../database/migrations/create_embeddings_table.php' => database_path('migrations/create_embeddings_table.php'),
            ], 'similar-content-migrations');
        }        
    }
}