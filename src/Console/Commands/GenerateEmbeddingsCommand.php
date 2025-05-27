<?php

namespace Timm49\LaravelSimilarContent\Console\Commands;

use Illuminate\Console\Command;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForRecord;
use Timm49\LaravelSimilarContent\SimilarContent;

class GenerateEmbeddingsCommand extends Command
{
    protected $signature = 'similar-content:generate-embeddings';
    protected $description = 'Generate embeddings for models with the HasEmbeddings attribute';

    public function handle()
    {
        foreach (SimilarContent::getRegisteredModels() as $model) {
            $count = $model::count();
            if (! $this->confirm("This will generate embeddings for {$count} records in {$model}. Do you want to continue?", true)) {
                $this->warn("Skipped model: {$model}");
                continue;
            }
            $model::all()->each(fn ($record) => GenerateEmbeddingsForRecord::dispatch($record));
            $this->info("Generated embeddings for model: " . $model);
        }

        return Command::SUCCESS;
    }
} 