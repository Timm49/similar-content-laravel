<?php

namespace Timm49\SimilarContentLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Timm49\SimilarContentLaravel\Console\Commands\GenerateEmbeddingsCommand;
use Timm49\SimilarContentLaravel\Console\Commands\InstallSimilarContentCommand;
use Timm49\SimilarContentLaravel\SimilarContent;

class SimilarContentProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/similar_content.php', 'similar_content'
        );

        $this->app->singleton(SimilarContent::class, function ($app) {
            return new SimilarContent();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->commands([
                GenerateEmbeddingsCommand::class,
                InstallSimilarContentCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../../database/migrations/create_embeddings_table.php' => database_path('migrations/create_embeddings_table.php'),
            ], 'similar-content-migrations');
        }        
    }
}