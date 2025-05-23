<?php

namespace Timm49\LaravelSimilarContent\Tests\Jobs;

use Illuminate\Support\Facades\Queue;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Embeddings\CreateResponse;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForModel;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Tests\Fixtures\EmbeddingTransformers\ArticleEmbeddingTransformer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    
    Queue::fake();
    OpenAI::fake([
        CreateResponse::fake([
            'object' => 'list',
            'data' => [
                [
                    'object' => 'embedding',
                    'index' => 0,
                    'embedding' => [
                        -0.008906792,
                        -0.013743395,
                    ],
                ],
            ],
            'usage' => [
                'prompt_tokens' => 8,
                'total_tokens' => 8,
            ],
        ]),
    ]);
});

it('generates embeddings for each record', function () {
    $job = new GenerateEmbeddingsForModel(Article::class, ArticleEmbeddingTransformer::class);
    $job->handle();

    OpenAI::assertSent(function (string $method, array $parameters) {
        return $method === 'embeddings.create' &&
               $parameters['model'] === 'text-embedding-3-small' &&
               !empty($parameters['input']);
    });
}); 