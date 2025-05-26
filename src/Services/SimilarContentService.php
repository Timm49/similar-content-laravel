<?php

namespace Timm49\LaravelSimilarContent\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimilarContentService
{
    private function generateEmbeddings(Model $model): array
    {
        $input = method_exists($model, 'getEmbeddingData')
            ? $model->getEmbeddingData()
            : $model->toJson();

        Log::info($input);
        
        $response = Http::withToken(config('similar_content.openai_api_key'))
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => 'text-embedding-3-small',
                'input' => $input,
            ])
            ->json();

        return $response['data'][0]['embedding'];
    }

    public function generateAndStoreEmbeddings(Model $model)
    {
        DB::table('embeddings')->updateOrInsert(
            [
                'embeddable_type' => get_class($model),
                'embeddable_id' => $model->id,
            ],
            [
                'data' => json_encode($this->generateEmbeddings($model)),
                'updated_at' => now(),
            ]
        );
    }
}