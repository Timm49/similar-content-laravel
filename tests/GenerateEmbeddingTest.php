<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Timm49\SimilarContentLaravel\Facades\SimilarContent;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;

class GenerateEmbeddingTest extends TestCase
{
    use RefreshDatabase;

    public function testGenerateEmbeddingJob()
    {
        Http::fake([
            'https://api.openai.com/v1/embeddings' => Http::response([
                'data' => [
                    ['embedding' => [0.1, 0.2, 0.3]],
                ],
            ]),
        ]);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'This is a test article',
        ]);

        SimilarContent::createEmbedding($article);
    }
}
