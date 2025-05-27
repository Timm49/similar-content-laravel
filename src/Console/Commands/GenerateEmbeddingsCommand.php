<?php

namespace Timm49\LaravelSimilarContent\Console\Commands;

use Illuminate\Console\Command;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForRecord;
use Timm49\LaravelSimilarContent\Services\SimilarContentService;
use Timm49\LaravelSimilarContent\SimilarContent;

class GenerateEmbeddingsCommand extends Command
{
    protected $signature = 'similar-content:generate-embeddings {--force : Skip confirmation prompts}';
    protected $description = 'Generate embeddings for models with the HasEmbeddings attribute';

    public function handle(SimilarContentService $embeddingService)
    {
        foreach (SimilarContent::getRegisteredModels() as $model) {
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
            $records->each(fn ($record) => $embeddingService->generateAndStoreEmbeddings($record));
            $this->info("Generated embeddings for model: " . $model);
        }

        return Command::SUCCESS;
    }
} 