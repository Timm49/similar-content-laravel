<?php

namespace Timm49\SimilarContentLaravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Jobs\GenerateAndStoreEmbeddingsJob;

class GenerateEmbeddingsCommand extends Command
{
    protected $signature = 'similar-content:generate-embeddings {model : The model you want to generate embeddings for} {--force : Skip confirmation prompts}';

    protected $description = 'Generate embeddings for a specified model';

    public function handle(): int
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

        if ($this->option('force')) {
            $records = $modelInstance::all();
        } else {
            $primaryKey = (new $modelInstance)->getKeyName();

            $records = $modelInstance::whereNotIn($primaryKey, function ($query) use ($modelInstance) {
                $query->select('embeddable_id')
                    ->from('embeddings')
                    ->where('embeddable_type', $modelInstance);
            })->get();
        }

        $count = $records->count();

        if ($count === 0) {
            $this->line("No records without embeddings found for model: {$modelClass}");
            return 1;
        }
        if (! $this->confirm("This will generate embeddings for {$count} records in {$modelClass}. Do you want to continue?", true)) {
            $this->warn("Skipped model: {$modelClass}");
            return 1;
        }

        $this->info("Embeddings generation job dispatched for $modelClass.");

        $records->each(fn ($record) => GenerateAndStoreEmbeddingsJob::dispatchSync($record));
        
        return 0;
    }
} 