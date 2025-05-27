# Laravel Similar Content

Easily generate and manage content embeddings for your Laravel models, enabling content similarity and recommendation functionality powered by AI.

Example News Feed Application using this package: [Timm49/example-app-similar-content](https://github.com/Timm49/example-app-similar-content)  

### What this package does

**1. Mark your models**

Add the `#[HasEmbeddings]` attribute to any Eloquent model you want to use for content similarity. This tells the package to generate embeddings for that model using the OpenAI Embeddings API.

**2. Generate and store embeddings**

Use the built-in Artisan command to generate embeddings for all existing records, or call a method in your application code to handle it automatically when new records are created or updated. The embeddings are stored in your database.

**3. Get similar content**

Retrieve similar items for any model like this:

```php
SimilarContent::for($model)->generateEmbeddings();
```

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

Add the SIMILAR_CONTENT_OPENAI_API_KEY to your .env file

```env
SIMILAR_CONTENT_OPENAI_API_KEY=some-key
```

## Usage

### Marking your models with embeddings

Simply annotate your Eloquent model with the `#[HasEmbeddings]` attribute. This automatically generates embeddings using a default transformation (concatenation of all fillable attributes) when running the artisan command.

```php
use Timm49\LaravelSimilarContent\Attributes\HasEmbeddings;

#[HasEmbeddings]
class Article extends Model
{
    protected $fillable = ['title', 'content'];
}
```

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

> 📘 It's very important to include the right data for the right embedding purposes. I've added some links at the bottom of this README which should be helpful to get familiar with vector databases, embedding, etc.

## Generating embeddings

Generate embeddings for all models using the #[HasEmbeddings] attribute.

```bash
php artisan similar-content:generate-embeddings
```

> ⚠ This will generate embeddings for ALL records of ALL models with the attribute. Depending on the amount of records in your database this can potentially be a long/expensive process since it will make a lot of API requests.

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

## Retrieving similar content

Now you can retrieve similar content for a specific record like so:

```php
SimilarContent::for($article)->getSimilarContent();
```

This will return an **array of `SimilarContentResult` objects**, each representing a similar record and its similarity score.

### `SimilarContentResult` structure

```php
class SimilarContentResult
{
    public function __construct(
        public readonly string $sourceType,      // Class name of the original model
        public readonly string $sourceId,        // ID of the original model
        public readonly string $targetType,      // Class name of the similar model
        public readonly string $targetId,        // ID of the similar model
        public readonly float $similarityScore,  // Value between 0 and 1 (1 = identical)
    ) {
    }
}
```

You can loop through the results like this:

```php
$results = SimilarContent::for($article)->getSimilarContent();

foreach ($results as $result) {
    echo "Found similar content (score: {$result->similarityScore}) with ID {$result->targetId}";
}
```

## ⚠️ Database & Performance Notes

This package **does not require a vector database** such as `pgvector`, `Pinecone`, or `Weaviate`. Instead, it stores embeddings in a regular database table (compatible with MySQL, PostgreSQL, etc.) and performs similarity comparisons in PHP.

**This approach has pros and cons:**

#### ✅ Benefits

* No need to set up or maintain a specialized vector database.
* Works out-of-the-box with your existing Laravel database setup.
* Easier to install, debug, and understand for most Laravel developers.

#### ⚠️ Considerations

* Since similarity comparisons are done in memory, **the package loads all embeddings of the same model** to calculate similarity scores.
* **This may cause performance issues** if your application contains a large number of embeddings for a given model.
* Not recommended for large-scale applications where millions of records need to be compared regularly. In such cases, a dedicated vector store (e.g., pgvector, Qdrant, Pinecone) may be more suitable.


## Useful resources

Here's some good resources to get you started:

- [Beginner Friendly Deep Dive On Vector Databases](https://www.dailydoseofds.com/a-beginner-friendly-and-comprehensive-deep-dive-on-vector-databases) Good to get you started
- [Evaluating Vector Databases 101](https://medium.com/tr-labs-ml-engineering-blog/evaluating-vector-databases-101-5f87a2366bb1) – A comprehensive guide to understanding vector DB architecture, indexing, filtering, ANN algorithms, and how to evaluate different options for production use.
- [OpenAI Embeddings Documentation](https://platform.openai.com/docs/guides/embeddings) Official OpenAI docs

## Alternatives

Similar packages with a slightly different approach:

- [5am-code/ada-laravel](https://github.com/5am-code/ada-laravel) Uses vector database

## Contributing

Contributions are welcome! Please submit issues and pull requests.

## License

MIT © Timm49
