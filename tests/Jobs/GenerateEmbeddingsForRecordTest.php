<?php

namespace Timm49\LaravelSimilarContent\Tests\Jobs;

use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Embeddings\CreateResponse;
use Timm49\LaravelSimilarContent\Jobs\GenerateEmbeddingsForRecord;
use Timm49\LaravelSimilarContent\Tests\Fixtures\Models\Article;
use Timm49\LaravelSimilarContent\Tests\Fixtures\EmbeddingTransformers\ArticleEmbeddingTransformer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('migrate:fresh');
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


it('generates embeddings for each record', function () {
    $article = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);

    $job = new GenerateEmbeddingsForRecord($article, ArticleEmbeddingTransformer::class);
    $job->handle();

    OpenAI::embeddings()->assertSent(function (string $method, array $parameters) use ($article) {
        return $parameters['input'] === $article->title . ' ' . $article->content;
    });
})->uses(RefreshDatabase::class)->in('.');


it('creates an embedding record for the article', function () {
    $article = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article',
    ]);

    $job = new GenerateEmbeddingsForRecord($article, ArticleEmbeddingTransformer::class);
    $job->handle();

    $embeddingRecord = DB::table('embeddings')->where('embeddable_id', $article->id)->first();

    expect($embeddingRecord)->not->toBeNull();
    expect($embeddingRecord->embeddable_type)->toBe(Article::class);
    expect($embeddingRecord->data)->toBeJson();

})->uses(RefreshDatabase::class)->in('.'); 