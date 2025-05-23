<?php

namespace Timm49\LaravelSimilarContent\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Attributes\HasSimilarContent;

#[HasSimilarContent]
class Comment extends Model
{
    protected $guarded = [];
} 