# Laravel Similar Content

Easily generate and manage content embeddings for your Laravel models, enabling content similarity and recommendation functionality powered by AI.

Example News Feed Application using this package: [Timm49/example-app-similar-content](https://github.com/Timm49/example-app-similar-content)  

### What this package does

**1. Mark your models**

[//]: # (Add the `#[HasEmbeddings]` attribute to any Eloquent model you want to use for content similarity. This tells the package to generate embeddings for that model using the OpenAI Embeddings API.)

**2. Generate and store embeddings**

Use the built-in Artisan command to generate embeddings for all existing records, or call a method in your application code to handle it automatically when new records are created or updated. The embeddings are stored in your database.

**3. Get similar content**

Retrieve similar content for a specific record and use it for a recommendation section.

## Installation

Install via Composer:

```bash
composer require timm49/similar-content-laravel
```


Run the installation command:

```bash
php artisan similar-content:install
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

### Queue Connection

To generate embeddings in the background when running the artisan command, add the following variable to your .env file:

```env
SIMILAR_CONTENT_QUEUE_CONNECTION=database
```

This automatically generates embeddings using a default transformation when running the artisan command.
The default transformation simply does: $model->toJson()

### Generating embeddings

#### Generate embeddings for existing records
This command will generate and store embeddings for all annotated models which are already in your database, and don't have embeddings yet.
```bash
php artisan similar-content:generate-embeddings
```

To generate embeddings for all records, use the --force flag:
```bash
php artisan similar-content:generate-embeddings --force
```

To generate and store embeddings for a record from within your application code, use this command:
```php
SimilarContent::createEmbedding($article);

```

## Retrieving similar content
Retrieving similar items for a record is as simple as:
```php
$results = SimilarContent::getSimilarContent($article);
foreach ($results as $result) {
    echo "Found similar content (score: {$result->similarityScore}) with ID {$result->targetId}";
}
```

This will return an **array of `SimilarContentResult` objects**, each representing a similar record and its similarity score.

## Advanced usage

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

### Customizing Embedding Data

The package ships with a `HasSimilarContent` trait which you can use in your models. You can customize the default behavior by overriding the `getEmbeddingData()` method:

```php
use Timm49\SimilarContentLaravel\Traits\HasSimilarContent;

class Article extends Model
{
    use HasSimilarContent;

    public function getEmbeddingData(): string
    {
        return $this->title . "\n" . $this->content;
    }
}
```

This gives you full control over:

- Which fields are included in the embedding
- How the data is formatted
- What text is used for similarity matching

> 📘 It's very important to include the right data for the right embedding purposes. I've added some links at the bottom of this README which should be helpful to get familiar with vector databases, embedding, etc.

## How similarity is calculated

This package uses cosine similarity to compare content embeddings. After generating an embedding (a high-dimensional vector) for each model, it calculates the similarity between two records by measuring the cosine of the angle between their vectors.

- A cosine similarity of 1 means the embeddings are identical (perfect match)
- A score of 0 means they are completely unrelated (orthogonal vectors)
- The result is a value between 0 and 1, where higher values indicate stronger similarity

All similarity comparisons are done in PHP by loading and comparing vectors in memory.

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
