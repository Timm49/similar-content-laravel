<?php

namespace Timm49\LaravelSimilarContent\Tests\Jobs;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Comment;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
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

it('it asks for a confirmation to generate embeddings for X amount of records', function () {
    Config::set('similar_content.models_path', __DIR__ . '/Fixtures/Models');

    Comment::create(['content' => 'This is a test comment',]);
    Comment::create(['content' => 'This is also a test comment',]);
    Comment::create(['content' => 'This is another test comment',]);

    $this->artisan('similar-content:generate-embeddings')
        ->expectsConfirmation('This will generate embeddings for 3 records in Timm49\\LaravelSimilarContent\\Tests\\Fixtures\\Models\\Comment. Do you want to continue?', 'yes')
        ->assertExitCode(0);
});

it('it skips records which already have embeddings', function () {
    Config::set('similar_content.models_path', __DIR__ . '/Fixtures/Models');

    $this->artisan('similar-content:generate-embeddings')
        ->expectsOutputToContain('No records without embeddings found for model: Timm49\\LaravelSimilarContent\\Tests\\Fixtures\\Models\\Comment')
        ->assertExitCode(0);
});