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
        foreach (SimilarContent::getRegisteredModels() as $embedModal) {
            $records = $embedModal::all();

            foreach ($records as $record) {
                GenerateEmbeddingsForRecord::dispatch($record);
                $this->info("Dispatched job for record: $record->id");
            }
        }

        return Command::SUCCESS;
    }
} 