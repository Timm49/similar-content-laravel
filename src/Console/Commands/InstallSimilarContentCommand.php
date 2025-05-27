<?php

namespace Timm49\LaravelSimilarContent\Console\Commands;

use Illuminate\Console\Command;

class InstallSimilarContentCommand extends Command
{
    protected $signature = 'similar-content:install';

    protected $description = 'Install the Laravel Similar Content package (publishes config & migrations)';

    public function handle(): int
    {
        $this->info('🔧 Publishing migration file...');

        $this->callSilent('vendor:publish', [
            '--provider' => "Timm49\\LaravelSimilarContent\\Providers\\SimilarContentProvider",
        ]);

        $this->info('✅ Migration file published.');

        $this->warn('⚠️  Don\'t forget to:');
        $this->line('- Add your OpenAI API key to your `.env` file as `SIMILAR_CONTENT_OPENAI_API_KEY=your-key-here`');
        $this->line('- Run `php artisan migrate` to create the `embeddings` table');

        $this->newLine();
        $this->info('✅ Laravel Similar Content is now installed!');

        return self::SUCCESS;
    }
}
