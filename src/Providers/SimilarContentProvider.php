<?php

namespace Timm49\LaravelSimilarContent\Providers;

use Illuminate\Support\ServiceProvider;
use Timm49\LaravelSimilarContent\SimilarContent;

class SimilarContentProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/similar_content.php', 'similar_content'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/similar_content.php' => config_path('similar_content.php'),
            ], 'similar-content-config');
        }
    }
}