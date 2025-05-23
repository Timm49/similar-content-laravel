<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForModel;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Post;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('similar_content.models_path', __DIR__ . '/Fixtures/Models');
    Queue::fake();
});

it('dispatches a job for each model with HasSimilarContent attribute', function () {

    Artisan::call('similar-content:generate-embeddings');

    Queue::assertPushed(GenerateEmbeddingsForModel::class, function ($job) {
        return $job->modelClass === Article::class;
    });

    Queue::assertNotPushed(GenerateEmbeddingsForModel::class, function ($job) {
        return $job->modelClass === Post::class;
    });
}); 