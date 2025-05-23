<?php

namespace Timm49\LaravelSimilarContent\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Attributes\HasSimilarContent;

#[HasSimilarContent(transformer: 'text-embedding-3-small')]
class Article extends Model
{
    protected $guarded = [];
} 