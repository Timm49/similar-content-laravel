<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

it('publishes the config file', function () {
    $configFilePath = config_path('similar_content.php');
    if (file_exists($configFilePath)) {
        unlink($configFilePath);
    }

    expect(file_exists($configFilePath))->toBeFalse();

    Artisan::call('vendor:publish', [
        '--provider' => 'Timm49\LaravelSimilarContent\Providers\SimilarContentProvider',
        '--tag' => 'similar-content-configuration',
    ]);

    expect(file_exists($configFilePath))->toBeTrue();
}); 