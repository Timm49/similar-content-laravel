<?php

namespace Timm49\LaravelSimilarContent\Services;

use OpenAI\Laravel\Facades\OpenAI;

class SimilarContentService
{
    public function generateEmbeddings(string $input): array
    {
        $apiKey = config('similar_content.openai_api_key');

        $client = $apiKey
            ? OpenAI::factory()->withApiKey($apiKey)->make()
            : OpenAI::factory()->make();

        $response = $client->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $input,
        ]);

        return $response['data'][0]['embedding'];
    }
}