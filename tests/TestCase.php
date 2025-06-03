<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase as Orchestra;
use Timm49\SimilarContentLaravel\SimilarContentProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/Database/Migrations');
        Artisan::call('migrate:fresh');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('similar_content.models_path', __DIR__ . '/Fixtures/Models');
        $app['config']->set('similar_content.auto_generate', false);
    }

    protected function getPackageProviders($app)
    {
        return [
            SimilarContentProvider::class,
        ];
    }

} 