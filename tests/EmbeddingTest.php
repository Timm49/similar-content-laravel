<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Timm49\SimilarContentLaravel\Models\Embedding;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;
use Timm49\SimilarContentLaravel\ValueObjects\EmbeddingVector;

it('casts value object', function () {
    $migrationPath = database_path('migrations/create_embeddings_table.php');

    $article = Article::create([
        'title' => 'Test Article',
        'content' => 'This is a test article about AI.',
    ]);

    $embedding = Embedding::create([
        'embeddable_id' => $article->id,
        'embeddable_type' => Article::class,
        'data' => new EmbeddingVector(fake()->toArray()),
    ]);

    $this->assertInstanceOf(EmbeddingVector::class, $embedding->data);
});