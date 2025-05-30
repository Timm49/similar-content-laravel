<?php

namespace Timm49\SimilarContentLaravel\Console\Commands;

use Illuminate\Console\Command;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;
use Timm49\SimilarContentLaravel\Jobs\GenerateAndStoreEmbeddingsJob;

class GenerateEmbeddingsCommand extends Command
{
    protected $signature = 'similar-content:generate-embeddings {--force : Skip confirmation prompts}';
    protected $description = 'Generate embeddings for models with the HasEmbeddings attribute';

    public function handle()
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

            $queueConnection = config('similar_content.queue_connection');
            $records->each(fn ($record) => $queueConnection
                ? GenerateAndStoreEmbeddingsJob::dispatch($record)->onConnection($queueConnection)
                : GenerateAndStoreEmbeddingsJob::dispatchSync($record)
            );

            $this->info("Generating embeddings for model: " . $model);
        }

        return Command::SUCCESS;
    }
} 