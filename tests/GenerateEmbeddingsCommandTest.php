<?php

namespace Timm49\SimilarContentLaravel\Tests\Jobs;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Timm49\SimilarContentLaravel\Console\Commands\GenerateEmbeddingsCommand;
use Timm49\SimilarContentLaravel\Jobs\GenerateAndStoreEmbeddingsJob;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Comment;

beforeEach(function () {
    Config::set('similar_content.models_path', __DIR__ . '/Fixtures/Models');
    Artisan::call('migrate:fresh');
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                ['embedding' => [0.5, 0.6, 0.7]],
            ],
        ]),
    ]);
    Queue::fake();
});

it('discovers models with HasEmbeddings attribute', function () {
    $modelsPath = __DIR__ . '/Fixtures/Models';
    $models = GenerateEmbeddingsCommand::getRegisteredModels($modelsPath);

    expect($models)->toHaveCount(2);
    expect($models[0])->toBe(Article::class);
    expect($models[1])->toBe(Comment::class);
});

it('asks for a confirmation to generate embeddings for X amount of records', function () {

    Comment::create(['content' => 'This is a test comment',]);
    Comment::create(['content' => 'This is also a test comment',]);
    Comment::create(['content' => 'This is another test comment',]);

    $this->artisan('similar-content:generate-embeddings')
        ->expectsConfirmation('This will generate embeddings for 3 records in Timm49\\SimilarContentLaravel\\Tests\\Fixtures\\Models\\Comment. Do you want to continue?', 'yes')
        ->assertExitCode(0);
});

it('skips records which already have embeddings', function () {
    $articleWithEmbeddings = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);

    DB::table('embeddings')->insert([
        [
            'embeddable_type' => Article::class,
            'embeddable_id' => (string)$articleWithEmbeddings->id,
            'data' => json_encode([0.1, 0.2, 0.3]),
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);

    $this->artisan('similar-content:generate-embeddings')
        ->expectsOutputToContain('No records without embeddings found for model: Timm49\\SimilarContentLaravel\\Tests\\Fixtures\\Models\\Comment')
        ->assertExitCode(0);
});

it('dispatches sync when no queue connection is configured', function () {
    Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);

    $this->artisan('similar-content:generate-embeddings --force')
        ->expectsConfirmation('This will generate embeddings for 1 records in Timm49\\SimilarContentLaravel\\Tests\\Fixtures\\Models\\Article. Do you want to continue?', 'yes');

    Queue::assertPushed(GenerateAndStoreEmbeddingsJob::class, fn (GenerateAndStoreEmbeddingsJob $job) => $job->connection === 'sync');
});

it('pushes jobs on the queue when queue is configured', function () {
    config(['similar_content.queue_connection' => 'redis']);

    Comment::create(['content' => 'This is a test comment',]);

    $this->artisan('similar-content:generate-embeddings')
        ->expectsConfirmation('This will generate embeddings for 1 records in Timm49\\SimilarContentLaravel\\Tests\\Fixtures\\Models\\Comment. Do you want to continue?', 'yes')
        ->assertExitCode(0);

    Queue::assertPushed(GenerateAndStoreEmbeddingsJob::class, fn (GenerateAndStoreEmbeddingsJob $job) => $job->connection === 'redis');
});