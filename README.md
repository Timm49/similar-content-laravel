# Laravel Similar Content

Easily generate and manage content embeddings for your Laravel models, enabling content similarity and recommendation functionality powered by AI.

### What happens:
- It creates an embedding for a model

## Installation

Install via Composer:

```bash
composer require timm49/similar-content
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --provider="Timm49\LaravelSimilarContent\Providers\SimilarContentProvider"
```

Then run migrations:

```bash
php artisan migrate
```

## Configuration

### OpenAI API Key

By default, the package will use your environment's default OpenAI API key 'OPENAI_API_KEY'. However, you can set a separate API key specifically for this package by adding the following to your `config/similar-content.php` file:

```php
[
    'openai_api_key' => env('MY_CUSTOM_OPENAI_API_KEY'),
]
```

## Usage

### Marking your models with embeddings

Simply annotate your Eloquent model with the `#[HasEmbeddings]` attribute:

```php
use Timm49\LaravelSimilarContent\Attributes\HasEmbeddings;

#[HasEmbeddings]
class Article extends Model
{
    protected $fillable = ['title', 'content'];
}
```

This automatically generates embeddings using a default transformation (concatenation of all fillable attributes) when running the generate command.

### Customizing Embedding Data

The package ships with a `HasSimilarContent` trait which you can use in your models. You can customize the default behavior by overriding the `getEmbeddingData()` method:

```php
use Timm49\LaravelSimilarContent\Traits\HasSimilarContent;

class Article extends Model
{
    use HasSimilarContent;

    public function getEmbeddingData(): string
    {
        // Only use title and content for embeddings
        return $this->title . "\n" . $this->content;
        
        // Or use specific fields with custom formatting
        // return "Title: {$this->title}\nSummary: {$this->summary}\nTags: " . implode(', ', $this->tags);
    }
}
```

This gives you full control over:
- Which fields are included in the embedding
- How the data is formatted
- What text is used for similarity matching

> 📘 It's very important to include the right data for the right embedding purposes. I will add some uesful links with more information on this later.

### Manual Embedding Generation

While the package provides an artisan command to generate embeddings for all marked models, you can also generate embeddings manually for specific models using the fluent interface:

```php
use Timm49\LaravelSimilarContent\SimilarContent;

// Generate and store embeddings for a model
SimilarContent::for($article)->generateEmbeddings();

// This is useful when you want to:
// - Generate embeddings after specific model updates
// - Control when embeddings are generated
// - Generate embeddings for specific models only
```

## Generating embeddings

Generate embeddings manually by running:

```bash
php artisan similar-content:generate-embeddings
```

This command scans all marked models and generates embeddings accordingly.

## Configuration

Publish and customize the configuration file if needed:

```bash
php artisan vendor:publish --tag="similar-content-config"
```

Customize your model discovery paths, default transformer, and other settings in the published config file.

## Contributing

Contributions are welcome! Please submit issues and pull requests.

## License

MIT © Timm49
