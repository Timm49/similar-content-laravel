<?php

namespace Timm49\SimilarContentLaravel\Services\EmbeddingApi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Timm49\SimilarContentLaravel\Contracts\EmbeddingApi;

class OpenAIEmbeddingApi implements EmbeddingApi
{
    public function embedModel(Model $model): array
    {
        $input = method_exists($model, 'getEmbeddingData')
            ? $model->getEmbeddingData()
            : $model->toJson();

        return $this->embed($input);
    }

    public function embed(string $value): array
    {
        $response = Http::withToken(config('similar_content.openai_api_key'))
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => 'text-embedding-3-small',
                'input' => $value,
            ])
            ->json();

        return $response['data'][0]['embedding'];
    }
}