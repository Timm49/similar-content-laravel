<?php

namespace Timm49\LaravelSimilarContent\Services;

use Illuminate\Support\Facades\Http;
class SimilarContentService
{
    public function generateEmbeddings(string $input): array
    {
        $response = Http::withToken(config('similar_content.openai_api_key'))
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => 'text-embedding-3-small',
                'input' => $input,
            ])
            ->json();

        return $response['data'][0]['embedding'];
    }
}