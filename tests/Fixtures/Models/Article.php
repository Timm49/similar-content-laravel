<?php

namespace Timm49\LaravelSimilarContent\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Attributes\HasEmbeddings;
use Timm49\LaravelSimilarContent\Traits\HasSimilarContent;

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