<?php

namespace Timm49\SimilarContentLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Timm49\SimilarContentLaravel\Attributes\HasEmbeddings;
use Timm49\SimilarContentLaravel\Console\Commands\GenerateEmbeddingsCommand;
use Timm49\SimilarContentLaravel\Console\Commands\InstallSimilarContentCommand;
use Timm49\SimilarContentLaravel\Observers\ModelObserver;
use Timm49\SimilarContentLaravel\Services\OpenAIEmbeddingApi;
use Timm49\SimilarContentLaravel\Services\SimilarContentDefaultDatabase;
use Timm49\SimilarContentLaravel\Services\SimilarContentManager;

class SimilarContentProvider extends ServiceProvider
{
    private static array $registeredModels = [];

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/similar_content.php', 'similar_content'
        );
        $this->app->singleton('similar-content', fn () => new SimilarContentManager(
            new OpenAIEmbeddingApi(),
            new SimilarContentDefaultDatabase(),
        ));
    }

    public function boot()
    {
        $this->registerModelEvents();

        if ($this->app->runningInConsole()) {

            $this->commands([
                GenerateEmbeddingsCommand::class,
                InstallSimilarContentCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/similar_content.php' => config_path('similar_content.php'),
            ], 'similar-content-config');

            $this->publishes([
                __DIR__.'/../database/migrations/create_embeddings_table.php' => database_path('migrations/create_embeddings_table.php'),
            ], 'similar-content-migrations');
        }
    }

    public static function getRegisteredModels(?string $path = null): array
    {
        self::$registeredModels = [];
        $path ??= config('similar_content.models_path', app_path('Models'));

        foreach (glob($path . '/*.php') as $file) {
            $className = self::extractNamespaceFromFile($file) . '\\' . basename($file, '.php');

            if (! class_exists($className) || ! is_subclass_of($className, Model::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);
            $attributes = $reflection->getAttributes(HasEmbeddings::class);

            if (! empty($attributes)) {
                self::$registeredModels[] = $className;
            }
        }

        return self::$registeredModels;
    }

    static function extractNamespaceFromFile($filePath): ?string {
        $fileContents = file_get_contents($filePath);
        $namespacePattern = '/namespace\s+([^;]+);/';

        if (preg_match($namespacePattern, $fileContents, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function registerModelEvents(): void
    {
        $useModelEventListeners = config('similar_content.auto_generate', false);

        if ($useModelEventListeners) {
            foreach (self::getRegisteredModels() as $model) {
                $model::observe(ModelObserver::class);
            }
        }
    }
}