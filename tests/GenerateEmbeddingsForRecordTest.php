<?php

namespace Timm49\LaravelSimilarContent\Tests\Jobs;

use Illuminate\Support\Facades\DB;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForRecord;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Services\SimilarContentService;
use Mockery;
use Illuminate\Support\Facades\Config;

it('creates an embedding record for the article', function () {
     
    Config::set('similar_content.openai_api_key', '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');
    
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