<?php

namespace Timm49\SimilarContentLaravel\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Attributes\HasEmbeddings;
use Timm49\SimilarContentLaravel\Traits\HasSimilarContent;

#[HasEmbeddings]
class Article extends Model
{
    use HasSimilarContent;
    
    protected $guarded = [];

    public function getEmbeddingData(): string
    {
        return $this->title . ' ' . $this->content;
    }
} 