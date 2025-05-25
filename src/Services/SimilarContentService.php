<?php

namespace Timm49\LaravelSimilarContent\Services;

use OpenAI\Laravel\Facades\OpenAI;

class SimilarContentService
{
    public function generateEmbeddings(string $input): array
    {
        $response = OpenAI::embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $input,
        ]);

        return $response['data'][0]['embedding'];
    }
}