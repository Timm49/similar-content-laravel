<?php

namespace Timm49\SimilarContentLaravel\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

afterEach(function () {
    File::delete(glob(database_path('migrations/*_create_embeddings_table.php')));
});

it('publishes the migration file', function () {
    $migrationPath = database_path('migrations/create_embeddings_table.php');
    
    Artisan::call('vendor:publish', [
        '--provider' => 'Timm49\\SimilarContentLaravel\\SimilarContentProvider',
        '--tag' => 'similar-content-migrations',
    ]);

    expect(File::exists($migrationPath))->toBeTrue();
}); 