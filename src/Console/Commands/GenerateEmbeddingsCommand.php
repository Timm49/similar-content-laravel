<?php

namespace Timm49\SimilarContentLaravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Attributes\HasEmbeddings;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;

class GenerateEmbeddingsCommand extends Command
{
    protected $signature = 'similar-content:generate-embeddings {--force : Skip confirmation prompts}';
    protected $description = 'Generate embeddings for a specified model';
    private static array $registeredModels = [];

    public function handle(): int
    {
        $errors = false;

        foreach (self::getRegisteredModels() as $modelClass) {
            if (!$this->handleModel($modelClass)) {
                $errors = true;
            }
        }

        return $errors ? 0 : 1;
    }

    private function handleModel(mixed $modelClass): bool
    {
        if (!class_exists($modelClass)) {
            $this->warn("Model class $modelClass does not exist.");
            return false;
        }

        $modelInstance = new $modelClass;

        if (!$modelInstance instanceof Model) {
            $this->warn("$modelClass is not a valid Eloquent model.");
            return false;
        }

        if ($this->option('force')) {
            $records = $modelInstance::all();
        } else {
            $primaryKey = (new $modelInstance)->getKeyName();

            $records = $modelInstance::whereNotIn($primaryKey, function ($query) use ($modelClass) {
                $query->select('embeddable_id')
                    ->from('embeddings')
                    ->where('embeddable_type', $modelClass);
            })->get();
        }

        $count = $records->count();

        if ($count === 0) {
            $this->line("No records without embeddings found for model: {$modelClass}");
            return false;
        }

        if (!$this->confirm("This will generate embeddings for {$count} records in {$modelClass}. Do you want to continue?", true)) {
            $this->warn("Skipped model: {$modelClass}");
            return false;
        }

        $this->info("Generating embeddings for $modelClass.");

        $records->each(fn($record) => SimilarContent::createEmbedding($record));

        return true;
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
} 