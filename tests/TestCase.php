<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Timm49\LaravelSimilarContent\Providers\SimilarContentProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loadMigrationsFrom(__DIR__.'/../Fixtures/Database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            SimilarContentProvider::class,
        ];
    }
} 