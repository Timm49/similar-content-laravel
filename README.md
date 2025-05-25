# Laravel Similar Content

Easily generate and manage content embeddings for your Laravel models, enabling content similarity and recommendation functionality powered by AI.


## Installation


Install via Composer:

```bash
composer require timm49/similar-content
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --provider="Timm49\SimilarContent\SimilarContentServiceProvider"
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

This is useful when you want to:
- Use a different API key for embeddings than your main application
- Track usage separately for embedding generation
- Apply different rate limits or billing

## Usage


### Marking your models with embeddings

Simply annotate your Eloquent model with the `#[HasEmbeddings]` attribute:

```php
use Timm49\SimilarContent\Attributes\HasEmbeddings;

#[HasEmbeddings]
class Article extends Model
{
    protected $fillable = ['title', 'content'];
}
```

This automatically generates embeddings using a default transformation (concatenation of all fillable attributes) when running the generate command.

### Customizing Embedding Data

By default, the package uses the `HasSimilarContentTrait` which generates embeddings from all model attributes. You can customize this behavior by overriding the `getEmbeddingData()` method:

```php
use Timm49\LaravelSimilarContent\Traits\HasSimilarContentTrait;

class Article extends Model
{
    use HasSimilarContentTrait;

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
