<?php

namespace Timm49\SimilarContentLaravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;

class GenerateAndStoreEmbeddingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Model $record
    ) {
    }

    public function handle()
    {
        DB::table('embeddings')->updateOrInsert(
            [
                'embeddable_type' => get_class($this->record),
                'embeddable_id' => $this->record->id,
            ],
            [
                'data' => json_encode(SimilarContent::generateEmbeddings($this->record)),
                'updated_at' => now(),
            ]
        );
    }
}