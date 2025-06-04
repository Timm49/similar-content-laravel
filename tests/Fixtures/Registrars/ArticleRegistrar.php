<?php

namespace Timm49\SimilarContentLaravel\Tests\Fixtures\Registrars;

use Timm49\SimilarContentLaravel\Contracts\Registrar;
use Timm49\SimilarContentLaravel\SimilarContentResult;
use Timm49\SimilarContentLaravel\Tests\Fixtures\Models\Article;

class ArticleRegistrar implements Registrar
{
    public function __construct(
        private string $model
    ) {
    }

    public function transform(array $results): array
    {
        return collect($results)->map(function (SimilarContentResult $result) {
            $sourceArticle = Article::find($result->sourceId);
            $targetArticle = Article::find($result->targetId);
            return array_merge((array)$result, [
                'source' => $sourceArticle->toArray(),
                'target' => $targetArticle->toArray(),
            ]);
        })->toArray();
    }

    public function getModel(): string
    {
        return $this->model;
    }
}