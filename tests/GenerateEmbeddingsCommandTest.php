<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForRecord;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Comment;

it('dispatches a job for each model with HasSimilarContent attribute', function () {
    Queue::fake();
    Config::set('similar_content.models_path', __DIR__ . '/Fixtures/Models');
    
    $comment = Comment::create([
        'content' => 'This is a test comment',
    ]);

    Artisan::call('similar-content:generate-embeddings');

    Queue::assertCount(1);
    Queue::assertPushed(GenerateEmbeddingsForRecord::class, fn ($job) => $job->record->is($comment));
}); 