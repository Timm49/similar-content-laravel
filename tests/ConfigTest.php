<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Illuminate\Support\Facades\File;

test('config file can be published', function () {
    // Remove the config file if it exists
    if (File::exists(config_path('similar_content.php'))) {
        File::delete(config_path('similar_content.php'));
    }

    // Assert the config file doesn't exist
    expect(File::exists(config_path('similar_content.php')))->toBeFalse();

    // Run the publish command
    $this->artisan('vendor:publish', [
        '--provider' => 'Timm49\LaravelSimilarContent\Providers\SimilarContentProvider',
        '--tag' => 'similar-content-config'
    ])->assertSuccessful();

    // Assert the config file exists
    expect(File::exists(config_path('similar_content.php')))->toBeTrue();

    // Assert the config file contains the expected content
    $config = require config_path('similar_content.php');
    
    expect($config)->toBeArray();
    expect($config['models'])->toBeArray();
    expect($config['models'])->toBeEmpty();
}); 