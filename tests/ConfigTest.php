<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

it('publishes the config file', function () {
    if (file_exists(config_path('similar_content.php'))) {
        unlink(config_path('similar_content.php'));
    }

    expect(file_exists(config_path('similar_content.php')))->toBeFalse();

    Artisan::call('vendor:publish', [
        '--provider' => 'Timm49\LaravelSimilarContent\Providers\SimilarContentProvider',
        '--tag' => 'similar-content-config',
    ]);

    expect(file_exists(config_path('similar_content.php')))->toBeTrue();

    $configContent = file_get_contents(config_path('similar_content.php'));
    expect($configContent)->toContain('return [', "'models_path' => app_path('Models'),", '];');
}); 