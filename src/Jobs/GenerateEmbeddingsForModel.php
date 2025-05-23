<?php

namespace Timm49\LaravelSimilarContent\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI\Laravel\Facades\OpenAI;
use Timm49\LaravelSimilarContent\Interfaces\EmbeddingTransformer;

class GenerateEmbeddingsForModel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $modelClass;
    public $transformer;

    public function __construct(string $modelClass, string $transformer)
    {
        $this->modelClass = $modelClass;
        $this->transformer = $transformer;
    }

    public function handle()
    {
        $modelClass = $this->modelClass;
        $transformer = app($this->transformer);

        $records = $modelClass::all();

        foreach ($records as $record) {
            $input = $transformer->getEmbeddingData($record);

            $response = OpenAI::embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $input,
            ]);

            // Process the response as needed
        }
    }
} 