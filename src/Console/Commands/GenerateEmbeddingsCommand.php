<?php

namespace Timm49\LaravelSimilarContent\Console\Commands;

use Illuminate\Console\Command;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForModel;
use Timm49\LaravelSimilarContent\SimilarContent;

class GenerateEmbeddingsCommand extends Command
{
    protected $signature = 'similar-content:generate-embeddings';

    protected $description = 'Generate embeddings for models with the HasSimilarContent attribute';

    public function handle()
    {
        foreach (SimilarContent::getRegisteredModels() as $embedModal) {
            GenerateEmbeddingsForModel::dispatch($embedModal->model, $embedModal->transformer);
            $this->info("Dispatched job for model: $embedModal->model");
        }

        return Command::SUCCESS;
    }
} 