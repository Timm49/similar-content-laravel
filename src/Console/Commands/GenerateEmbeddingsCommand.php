<?php

namespace Timm49\SimilarContentLaravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Jobs\GenerateAndStoreEmbeddingsJob;

class GenerateEmbeddingsCommand extends Command
{
    protected $signature = 'similar-content:generate-embeddings {model : The model you want to generate embeddings for} {--force : Skip confirmation prompts}';

    protected $description = 'Generate embeddings for a specified model';

    public function handle()
    {
        $modelClass = $this->argument('model');

        if (!class_exists($modelClass)) {
            $this->error("Model class $modelClass does not exist.");
            return 1;
        }

        $modelInstance = new $modelClass;

        if (!$modelInstance instanceof Model) {
            $this->error("$modelClass is not a valid Eloquent model.");
            return 1;
        }

        $this->info("Embeddings generation job dispatched for $modelClass.");

        GenerateAndStoreEmbeddingsJob::dispatch($modelInstance);
        return 0;
    }
} 