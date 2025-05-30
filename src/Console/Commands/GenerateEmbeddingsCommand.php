<?php

namespace Timm49\SimilarContentLaravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Attributes\HasEmbeddings;
use Timm49\SimilarContentLaravel\Jobs\GenerateAndStoreEmbeddingsJob;

class GenerateEmbeddingsCommand extends Command
{
    protected $signature = 'similar-content:generate-embeddings {--force : Skip confirmation prompts}';
    protected $description = 'Generate embeddings for models with the HasEmbeddings attribute';

    private static array $registeredModels = [];
    
    public function handle()
    {
        foreach (self::getRegisteredModels() as $model) {
            if ($this->option('force')) {
                $records = $model::all();
            } else {
                $primaryKey = (new $model)->getKeyName();

                $records = $model::whereNotIn($primaryKey, function ($query) use ($model) {
                    $query->select('embeddable_id')
                        ->from('embeddings')
                        ->where('embeddable_type', $model);
                })->get();
            }

            $count = $records->count();

            if ($count === 0) {
                $this->line("No records without embeddings found for model: {$model}");
                continue;
            }
            if (! $this->confirm("This will generate embeddings for {$count} records in {$model}. Do you want to continue?", true)) {
                $this->warn("Skipped model: {$model}");
                continue;
            }

            $queueConnection = config('similar_content.queue_connection');
            $records->each(fn ($record) => $queueConnection
                ? GenerateAndStoreEmbeddingsJob::dispatch($record)->onConnection($queueConnection)
                : GenerateAndStoreEmbeddingsJob::dispatchSync($record)
            );

            $this->info("Generating embeddings for model: " . $model);
        }

        return Command::SUCCESS;
    }



    public static function getRegisteredModels(?string $path = null): array
    {
        self::$registeredModels = [];
        $path ??= config('similar_content.models_path', app_path('Models'));

        foreach (glob($path . '/*.php') as $file) {
            $className = self::extractNamespaceFromFile($file) . '\\' . basename($file, '.php');

            if (! class_exists($className)) {
                continue;
            }

            if (! is_subclass_of($className, Model::class)) {
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
} 