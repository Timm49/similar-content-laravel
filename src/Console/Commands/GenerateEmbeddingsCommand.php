<?php

namespace Timm49\LaravelSimilarContent\Console\Commands;

use Illuminate\Console\Command;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForRecord;
use Timm49\LaravelSimilarContent\SimilarContent;
class GenerateEmbeddingsCommand extends Command
{
    protected $signature = 'similar-content:generate-embeddings';
    protected $description = 'Generate embeddings for models with the HasSimilarContent attribute';

    public function handle()
    {
        foreach (SimilarContent::getRegisteredModels() as $model) {
            $model::limit(3)->get()->each(fn ($record) => GenerateEmbeddingsForRecord::dispatch($record));
            $this->info("Generated embeddings for model: " . $model);
        }

        return Command::SUCCESS;
    }
} 