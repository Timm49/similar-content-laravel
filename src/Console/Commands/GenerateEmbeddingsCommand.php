<?php

namespace Timm49\SimilarContentLaravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;
use Timm49\SimilarContentLaravel\SimilarContentProvider;

class GenerateEmbeddingsCommand extends Command
{
    protected $signature = 'similar-content:generate-embeddings {--force : Skip confirmation prompts}';
    protected $description = 'Generate embeddings for a specified model';

    public function handle(): int
    {
        $errors = false;

        foreach (SimilarContentProvider::getRegisteredModels() as $modelClass) {
            if (!$this->handleModel($modelClass)) {
                $errors = true;
            }
        }

        return $errors ? 0 : 1;
    }

    private function handleModel(string $modelClass): bool
    {
        $records = $this->getRecords($modelClass);
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

    private function getRecords(string $modelClass): Collection
    {
        $modelInstance = new $modelClass;

        if ($this->option('force')) {
            return $modelInstance::all();
        }

        $primaryKey = (new $modelInstance)->getKeyName();

        return $modelInstance::whereNotIn($primaryKey, function ($query) use ($modelClass) {
            $query->select('embeddable_id')
                ->from('embeddings')
                ->where('embeddable_type', $modelClass);
        })->get();
    }
}