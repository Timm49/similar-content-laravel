<?php

namespace Timm49\LaravelSimilarContent\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Timm49\LaravelSimilarContent\Services\SimilarContentService;

class GenerateEmbeddingsForRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $record;

    public function __construct($record)
    {
        $this->record = $record;
    }

    public function handle(SimilarContentService $embeddingService)
    {
        $embedding = $embeddingService->generateEmbeddings($this->record->getEmbeddingData());
    
        DB::table('embeddings')->insert([
            'embeddable_type' => get_class($this->record),
            'embeddable_id' => $this->record->id,
            'data' => json_encode($embedding),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
} 