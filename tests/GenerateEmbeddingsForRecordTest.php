<?php

namespace Timm49\LaravelSimilarContent\Tests\Jobs;

use Illuminate\Support\Facades\DB;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForRecord;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Services\SimilarContentService;
use Mockery;

it('creates an embedding record for the article', function () {
     
     $article = Article::create([
         'title' => 'Test Article',
         'content' => 'This is a test article',
     ]);

    $mock = Mockery::mock(SimilarContentService::class);
    $mock->shouldReceive('generateEmbeddings')->andReturn([0.1, 0.2, 0.3]);
    $job = new GenerateEmbeddingsForRecord($article);


    $job->handle($mock);

    $embeddingRecord = DB::table('embeddings')->where('embeddable_id', $article->id)->first();

    expect($embeddingRecord)->not->toBeNull();
    expect($embeddingRecord->embeddable_type)->toBe(Article::class);
    expect($embeddingRecord->data)->toBeJson();
});