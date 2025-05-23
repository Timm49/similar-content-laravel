<?php

namespace Timm49\LaravelSimilarContent\Providers;

use Illuminate\Support\ServiceProvider;
use Timm49\LaravelSimilarContent\SimilarContent;
use Timm49\LaravelSimilarContent\Console\Commands\GenerateEmbeddingsCommand;

class SimilarContentProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/similar_content.php', 'similar_content'
        );

        SimilarContent::discoverModelsWithEmbeddings(config('similar_content.models_path'));
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateEmbeddingsCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../../config/similar_content.php' => config_path('similar_content.php'),
            ], 'similar-content-config');
        }
    }
}