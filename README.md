# Similar Content Laravel

Easily generate and manage content embeddings for your Laravel models, enabling content similarity and recommendation functionality.

## What this package does

**Generate embeddings of your databases content**

Use the built-in Artisan command to generate embeddings for all existing records for all defined models. Once that's done you'll probably want to add a couple of lines your application code to handle it automatically when new records are created or updated. The embeddings are stored in your database.
Optionally, you can enable auto_generation, which generates embeddings automatically using model events (saved).

**Get similar content**

Retrieve similar content for a specific record and use it for a recommendation section, semantic searching or any other embedding related feature.

### See it in action
A simple example application using this package can be found here: [timm49/example-app-similar-content](https://github.com/Timm49/example-app-similar-content)

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

config/similar_content.php

```php
return [
    'openai_api_key' => env('SIMILAR_CONTENT_OPENAI_API_KEY'), // OpenAI API Key
    'auto_generate' => false, // auto generate embeddings when a model is created/updated
    'cache_enabled' => false, // cache the similar content results
    'cache_ttl' => 3600,   // default ttl in seconds
];
```

Add the SIMILAR_CONTENT_OPENAI_API_KEY to your .env file

```env
SIMILAR_CONTENT_OPENAI_API_KEY=some-key
```

### Auto Generate
If you want the package to automatically create/update embeddings using model events, you can do so by setting 'auto_generate' in the config to true.
> Use carefully as this will call the API on every "saved" hook!

### Mark the models you want to retrieve similar content for

Add the #[HasEmbeddings] attribute to the models you want to store embeddings for. Embeddings are necessary to find and compare similar items.
```php
use Timm49\SimilarContentLaravel\Attributes\HasEmbeddings;

#[HasEmbeddings]
class Article extends Model
{
    use HasSimilarContent;
}
```

### Generating embeddings for existing records

Run the artisan command to generate and store embeddings for the marked models:
```bash
php artisan similar-content:generate-embeddings
```

To generate embeddings for all records, including existing items, use the --force flag:
```bash
php artisan similar-content:generate-embeddings --force
```

To generate and store embeddings for a record from within your application code, use this:
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

```php
readonly class SimilarContentResult
{
    public function __construct(
        public string $sourceType,      // Class name of the original model
        public string $sourceId,        // ID of the original model
        public string $targetType,      // Class name of the similar model
        public string $targetId,        // ID of the similar model
        public float $similarityScore,  // Value between 0 and 1 (1 = identical)
    ) {
    }
}
```

## Semantic search
You can also use this package to find records similar to a search query string, enabling semantic search in your application. Unlike traditional search, which matches exact words or phrases, semantic search understands the meaning behind the query — allowing users to find relevant results even if the wording doesn't exactly match.

Behind the scenes, the package sends the query (e.g. 'Donald Trump') to the OpenAI API to generate an embedding and compares it to your stored model embeddings.

Retrieving content similar to a query is as simple as:
```php
SimilarContent::search('Donald Trump');
```
> This will search for similar content for ALL target types (models)

This will return an **array of `SearchResult` objects**, each representing a similar record and its similarity score.

```php
readonly class SearchResult
{
    public function __construct(
        public string $id,             // ID of the similar model
        public string $type,           // Class name of the similar model
        public float $similarityScore, // Value between 0 and 1 (1 = identical)
    ) {
    }
}
```

To search only in "articles" and "pages", you can pass an array of models:
```php
SimilarContent::search('Donald Trump', [
    Article::class,
    Page::class,
]);
```

## Customizing Embedding Data
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
This package **does not require a dedicated vector database** but now also supports pgvector for PostgreSQL users, in addition to working with standard SQL databases like MySQL and SQLite.

#### ✅ Benefits

* Works out-of-the-box with your existing Laravel database setup — embeddings are stored in your current database, not a separate one.
* No need to maintain a separate vector store unless needed.
* With pgvector, similarity scoring can be offloaded to the database, improving performance for larger datasets.

#### ⚠️ Considerations

There are two different ways similarity is calculated, depending on your database setup:

#### PostgreSQL with pgvector support:
* If you're using the pgvector extension, the package leverages native vector operations.
* This allows similarity scores to be calculated directly in the database via SQL (e.g., 1 - (data <#> '...')), making queries significantly faster and scalable.
* **Note:** Due to differences in data types and precision handling, slight variations in similarity scores may occur compared to standard SQL databases.

#### Standard SQL databases (MySQL, SQLite, non-pgvector PostgreSQL, etc.):
* The package loads all embeddings for the relevant model into memory and performs cosine similarity comparisons in PHP.
* This can lead to performance issues with a large number of records.
* Suitable for smaller or medium-sized datasets, but may not scale well with millions of embeddings.

> ℹ️ If you're running PostgreSQL and expect to work with a high volume of embeddings, enabling pgvector is highly recommended for better performance and scalability.

## Roadmap

* [ ] Add limit_similar_results to config
* [ ] Add limit_search_results to config
* [ ] Add `SimilarContent::fake()` method

## Useful resources

Here's some good resources to get you started:

- [Example Application](https://github.com/Timm49/example-app-similar-content) A Laravel application showcasing this package on dummy content. You can see the similarity results in action on both News Articles content, as well as ecommerce products.
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
