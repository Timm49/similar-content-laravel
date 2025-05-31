<?php

namespace Timm49\SimilarContentLaravel\Tests\Jobs;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Timm49\SimilarContentLaravel\Console\Commands\GenerateEmbeddingsCommand;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;
use Timm49\SimilarContentLaravel\Models\Embedding;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Comment;

function fake(int $dimensions = 1536): array
{
    return [0.1, 0.2, 0.3];
}

beforeEach(function () {
    Config::set('similar_content.models_path', __DIR__ . '/Fixtures/Models');
    Artisan::call('migrate:fresh');
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
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                [
                    'index' => 0,
                    'embedding' => fake()
                ],
            ],
        ]),
    ]);

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

    Embedding::insert([
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

it('creates embeddings for a single record', function () {
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                [
                    'index' => 0,
                    'embedding' => fake()
                ],
            ],
        ]),
    ]);

    Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);

    SimilarContent::shouldReceive('createEmbedding')->once();

    $this->artisan('similar-content:generate-embeddings --force')
        ->expectsConfirmation('This will generate embeddings for 1 records in Timm49\\SimilarContentLaravel\\Tests\\Fixtures\\Models\\Article. Do you want to continue?', 'yes');
});

it('creates multiple embeddings in one call', function () {
    Http::fake([
        'https://api.openai.com/v1/embeddings' => Http::response([
            'data' => [
                [
                    'index' => 0,
                    'embedding' => fake()
                ],
                [
                    'index' => 1,
                    'embedding' => fake()
                ],
            ],
        ]),
    ]);

    Article::create([
        'title' => 'Test Article 1',
        'content' => 'This is a test article 1',
    ]);

    Article::create([
        'title' => 'Test Article 2',
        'content' => 'This is a test article 2',
    ]);
    
    $this->artisan('similar-content:generate-embeddings --force')
        ->expectsConfirmation('This will generate embeddings for 2 records in Timm49\\SimilarContentLaravel\\Tests\\Fixtures\\Models\\Article. Do you want to continue?', 'yes');

    $this->assertDatabaseCount('embeddings', 2);
});