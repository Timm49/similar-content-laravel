<?php

namespace Timm49\LaravelSimilarContent\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Attributes\HasSimilarContent;
use Timm49\LaravelSimilarContent\Traits\HasSimilarContentTrait;

#[HasSimilarContent]
class Comment extends Model
{
    use HasSimilarContentTrait;

    protected $guarded = [];
} 