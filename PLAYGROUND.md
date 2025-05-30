1. Generate and store embeddings for a model
```php
SimilarContent::createEmbedding($model);

$model->createEmbedding();
```

2. Retrieve similar content
```php
$similarContent = SimilarContent::getSimilarContent($model);
// or
$similarContent = $model->getSimilarContent();
```