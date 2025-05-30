<?php

namespace Timm49\SimilarContentLaravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Timm49\SimilarContentLaravel\Contracts\EmbeddingApi;

class OpenAIEmbeddingApi implements EmbeddingApi
{
    public function generateEmbeddings(Model $model): array
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
}