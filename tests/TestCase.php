<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Timm49\LaravelSimilarContent\Providers\SimilarContentProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/Database/Migrations');
        
    }

    protected function getPackageProviders($app)
    {
        return [
            SimilarContentProvider::class,
        ];
    }

} 