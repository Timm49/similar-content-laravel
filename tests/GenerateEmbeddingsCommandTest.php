<?php

namespace Timm49\LaravelSimilarContent\Tests\Jobs;

use Illuminate\Support\Facades\Config;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForRecord;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Comment;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    Http::fake();
    Queue::fake();
});

it('dispatches a job for each model with HasEmbeddings attribute', function () {
    Config::set('similar_content.models_path', __DIR__ . '/Fixtures/Models');

    $comment = Comment::create([
        'content' => 'This is a test comment',
    ]);

    $this->artisan('similar-content:generate-embeddings')
        ->expectsConfirmation('This will generate embeddings for 0 records in Timm49\\LaravelSimilarContent\\Tests\\Fixtures\\Models\\Article. Do you want to continue?', 'yes')
        ->expectsConfirmation('This will generate embeddings for 1 records in Timm49\\LaravelSimilarContent\\Tests\\Fixtures\\Models\\Comment. Do you want to continue?', 'yes')
        ->assertExitCode(0);

    Queue::assertCount(1);
    Queue::assertPushed(GenerateEmbeddingsForRecord::class, fn ($job) => $job->record->is($comment));
}); 