<?php

namespace Timm49\LaravelSimilarContent\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up any previously published files
    File::delete(config_path('similar-content.php'));
    collect(File::files(database_path('migrations')))
        ->filter(fn ($file) => str_contains($file->getFilename(), 'create_embeddings_table'))
        ->each(fn ($file) => File::delete($file->getPathname()));
});

it('publishes config and migration and outputs installation reminders', function () {
    // Run the install command
    $exitCode = Artisan::call('similar-content:install');

    // Assert success exit code
    expect($exitCode)->toBe(0);

    // Check that config file was published
    expect(File::exists(config_path('similar-content.php')))->toBeTrue();

    // Check that the migration file was published
    $migrationPublished = collect(File::files(database_path('migrations')))
        ->contains(fn ($file) => str_contains($file->getFilename(), 'create_embeddings_table'));
    expect($migrationPublished)->toBeTrue();

    // Assert command output includes reminders
    Artisan::call('similar-content:install'); // Run again to capture output
    $output = Artisan::output();

    expect($output)->toContain('Add your OpenAI API key')
        ->toContain('Run `php artisan migrate`')
        ->toContain('Laravel Similar Content is now installed');
});
