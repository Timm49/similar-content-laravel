<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Timm49\LaravelSimilarContent\DefaultEmbeddingTransformer;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForRecord;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Comment;
use Illuminate\Support\Facades\Config;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Embeddings\CreateResponse;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    Config::set('similar_content.models_path', __DIR__ . '/Fixtures/Models');
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

it('dispatches a job for each model with HasSimilarContent attribute', function () {

    $comment = Comment::create([
        'content' => 'This is a test article',
    ]);

    Artisan::call('similar-content:generate-embeddings');

    Queue::assertCount(1);
    Queue::assertPushed(GenerateEmbeddingsForRecord::class, function ($job) use ($comment) {
        return $job->record->is($comment) &&
            $job->transformer === DefaultEmbeddingTransformer::class;
    });
}); 