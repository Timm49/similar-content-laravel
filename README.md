# Laravel Similar Content

Easily generate and manage content embeddings for your Laravel models, enabling content similarity and recommendation functionality powered by AI.

---

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

---

## Configuration

### OpenAI API Key

By default, the package will use your application's default OpenAI API key. However, you can set a separate API key specifically for this package by adding the following to your `.env` file:

```env
SIMILAR_CONTENT_OPENAI_API_KEY=your-api-key-here
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

This automatically generates embeddings using a default transformation (concatenation of all fillable attributes).

### Using a custom Transformer (optional)

If you need custom embedding content, specify a transformer:

```php
use Timm49\SimilarContent\Attributes\HasEmbeddings;
use App\Transformers\ArticleEmbeddingTransformer;

#[HasEmbeddings(transformer: ArticleEmbeddingTransformer::class)]
class Article extends Model
{
    // ...
}
```

Implement your custom transformer:

```php
use Timm49\SimilarContent\Contracts\EmbeddingTransformer;
use Illuminate\Database\Eloquent\Model;

class ArticleEmbeddingTransformer implements EmbeddingTransformer
{
    public function transform(Model $model): string
    {
        return $model->title . "\n" . strip_tags($model->content);
    }
}
```

---

## Generating embeddings

Generate embeddings manually by running:

```bash
php artisan similar-content:generate-embeddings
```

This command scans all marked models and generates embeddings accordingly.

---

## Configuration

Publish and customize the configuration file if needed:

```bash
php artisan vendor:publish --tag="similar-content-config"
```

Customize your model discovery paths, default transformer, and other settings in the published config file.

---

## Advanced usage

Access embeddings directly on your models:

```php
$article = Article::first();
$embedding = $article->embedding; // returns embedding vector
```

---

## Contributing

Contributions are welcome! Please submit issues and pull requests.

---

## License

MIT © Timm49
