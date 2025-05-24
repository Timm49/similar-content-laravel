<?php

namespace Timm49\LaravelSimilarContent\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Attributes\HasSimilarContent;
use Timm49\LaravelSimilarContent\Traits\HasSimilarContentTrait;

#[HasSimilarContent]
class Article extends Model
{
    use HasSimilarContentTrait;
    
    protected $guarded = [];

    public function getEmbeddingData(): string
    {
        return $this->title . ' ' . $this->content;
    }
} 