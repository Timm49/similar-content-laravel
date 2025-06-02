<?php

namespace Timm49\SimilarContentLaravel\Tests\Jobs;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
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
});

it('checks how many records will be created and asks for confirmation', function () {
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
                [
                    'ß' => 2,
                    'embedding' => fake()
                ],
            ],
        ]),
    ]);

    Comment::create(['content' => 'This is a test comment',]);
    Comment::create(['content' => 'This is also a test comment',]);
    Comment::create(['content' => 'This is another test comment',]);

    $this->artisan('similar-content:generate-embeddings')
        ->expectsConfirmation('This will generate embeddings for 3 records in Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Comment. Do you want to continue?', 'Yes');
});

it('skips records which already have embeddings', function () {
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

    $this->artisan("similar-content:generate-embeddings")
        ->expectsOutputToContain('No records without embeddings found for model')
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

    $this->artisan("similar-content:generate-embeddings --force")
        ->expectsConfirmation('This will generate embeddings for 1 records in Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article. Do you want to continue?', 'Yes');;

    $this->assertDatabaseCount('embeddings', 1);
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

    $this->artisan("similar-content:generate-embeddings --force")
        ->expectsConfirmation('This will generate embeddings for 2 records in Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article. Do you want to continue?', 'Yes');

    $this->assertDatabaseCount('embeddings', 2);
});
