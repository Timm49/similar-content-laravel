<?php

namespace Timm49\LaravelSimilarContent\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

        $records = $modelClass::all();

        foreach ($records as $record) {
            GenerateEmbeddingsForRecord::dispatch($record, $this->transformer);
        }
    }
} 