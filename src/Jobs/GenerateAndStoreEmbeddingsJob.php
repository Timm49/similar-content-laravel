<?php

namespace Timm49\SimilarContentLaravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
        SimilarContent::createEmbedding($this->record);
    }
}