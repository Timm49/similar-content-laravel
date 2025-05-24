<?php

namespace Timm49\LaravelSimilarContent\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\DB;

class GenerateEmbeddingsForRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $record;

    public function __construct($record)
    {
        $this->record = $record;
    }

    public function handle()
    {
        $input = $this->record->getEmbeddingData();

        $response = OpenAI::embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $input,
        ]);

        $embedding = $response['data'][0]['embedding'];

        // Store the embedding in the database
        DB::table('embeddings')->insert([
            'embeddable_type' => get_class($this->record),
            'embeddable_id' => $this->record->id,
            'data' => json_encode($embedding),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
} 