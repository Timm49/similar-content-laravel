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
        foreach (SimilarContentProvider::getRegisteredModels() as $modelClass) {
            $this->generateEmbeddingsForModel($modelClass);
        }

        return 1;
    }

    private function generateEmbeddingsForModel(string $modelClass): void
    {
        $records = $this->getRecords($modelClass);

        if ($records->count() === 0) {
            $this->line("No records without embeddings found for model: {$modelClass}");
            return;
        }

        if (!$this->confirm("This will generate embeddings for {$records->count()} records in {$modelClass}. Do you want to continue?", true)) {
            $this->warn("Skipped model: {$modelClass}");
            return;
        }

        $this->info("Generating embeddings for $modelClass.");

        $records->each(fn($record) => SimilarContent::createEmbedding($record));

        $this->info("✅ Embeddings generated for $modelClass.");
    }

    private function getRecords(string $modelClass): Collection
    {
        $modelInstance = new $modelClass;

        if ($this->option('force')) {
            return $modelInstance::all();
        }

        $primaryKey = (new $modelInstance)->getKeyName();

        return $modelInstance::whereNotIn($primaryKey, fn($query) => $query->select('embeddable_id')
            ->from('embeddings')
            ->where('embeddable_type', $modelClass)
        )->get();
    }
}