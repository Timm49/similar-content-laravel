<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('publishes the migration file', function () {
    $migrationPath = database_path('migrations/create_embeddings_table.php');

    if (File::exists($migrationPath)) {
        File::delete($migrationPath);
    }

    expect(File::exists($migrationPath))->toBeFalse();

    Artisan::call('vendor:publish', [
        '--provider' => 'Timm49\\LaravelSimilarContent\\Providers\\SimilarContentProvider',
        '--tag' => 'similar-content-migrations',
    ]);

    expect(File::exists($migrationPath))->toBeTrue();
}); 