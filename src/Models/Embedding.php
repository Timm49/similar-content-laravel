<?php

namespace Timm49\SimilarContentLaravel\Models;

use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Casts\EmbeddingVectorCast;

class Embedding extends Model
{
    protected $fillable = [
        'embeddable_id',
        'embeddable_type',
        'data',
    ];
    
    protected $casts = [
        'data' => EmbeddingVectorCast::class,
    ];
}