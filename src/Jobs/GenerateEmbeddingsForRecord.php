<?php

namespace Timm49\LaravelSimilarContent\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Timm49\LaravelSimilarContent\Services\SimilarContentService;

class GenerateEmbeddingsForRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Model $record
    ) {
    }

    public function handle(SimilarContentService $embeddingService)
    {
        DB::table('embeddings')->insert([
            'embeddable_type' => get_class($this->record),
            'embeddable_id' => $this->record->id,
            'data' => json_encode($embeddingService->generateEmbeddings(
                $this->record->getEmbeddingData()
            )),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
} 