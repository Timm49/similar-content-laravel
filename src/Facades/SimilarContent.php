<?php

namespace Timm49\SimilarContentLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class SimilarContent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'similar-content';
    }
}