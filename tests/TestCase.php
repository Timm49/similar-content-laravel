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

        // TODO: Figure out why sqlite needs the migrate:fresh command to work
        if (env('DB_CONNECTION') === 'sqlite') {
            Artisan::call('migrate:fresh');
        }
    }
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('similar_content.models_path', __DIR__ . '/Fixtures/Models');
        $app['config']->set('similar_content.auto_generate', false);

        $app['config']->set('database.connections.pgsql', [
            'driver' => 'pgsql',
            'host' => env('PGVECTOR_DB_HOST', '127.0.0.1'),
            'port' => env('PGVECTOR_DB_PORT', '5432'),
            'database' => env('PGVECTOR_DB_DATABASE', 'laravel_db'),
            'username' => env('PGVECTOR_DB_USERNAME', ''),
            'password' => env('PGVECTOR_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            SimilarContentProvider::class,
        ];
    }

} 