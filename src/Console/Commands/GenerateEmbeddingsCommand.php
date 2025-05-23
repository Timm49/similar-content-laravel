<?php

namespace Timm49\LaravelSimilarContent\Console\Commands;

use Illuminate\Console\Command;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForModel;
use Timm49\LaravelSimilarContent\SimilarContent;

class GenerateEmbeddingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'similar-content:generate-embeddings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for models with the HasSimilarContent attribute';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $models = SimilarContent::discoverModelsWithEmbeddings(config('similar_content.models_path'));
        
        foreach ($models as $modelClass) {
            GenerateEmbeddingsForModel::dispatch($modelClass);
            $this->info("Dispatched job for model: $modelClass");
        }

        return Command::SUCCESS;
    }
} 