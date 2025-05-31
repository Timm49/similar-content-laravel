<?php

namespace Timm49\SimilarContentLaravel\Models;

use Illuminate\Database\Eloquent\Model;

class Embedding extends Model
{
    protected $fillable = [
        'embeddable_id',
        'embeddable_type',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}