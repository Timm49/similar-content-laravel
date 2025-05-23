<?php

namespace Timm49\LaravelSimilarContent\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Attributes\HasEmbeddings;

#[HasEmbeddings]
class Article extends Model
{
    protected $guarded = [];
} 