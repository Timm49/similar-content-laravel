### ✅ Done
* [x] Use `#[HasSimilarContent]` to mark models for embedding generation
* [x] Publish migrations
* [x] Publish configs
* [x] Generating embeddings (via command)
* [x] Include a trait which developers can use to overwrite the default `getEmbeddingData()` logic
* [x] Add `SimilarContentResult` value object for similarity results
* [x] Chain `SimilarContent::for($model)` to return an `EmbeddingContext`
* [x] `EmbeddingContext::findSimilar()` method
* [x] Retrieve similar content

### 🚨 Needed for MVP
* [ ] Add `SIMILAR_CONTENT_OPENAI_API_KEY` to config to allow a separate OpenAI key from the app
* [ ] Add `php artisan similar-content:install` command to publish config/migrations and show helpful setup reminders
* [ ] README: explain what happens in intro (storing in database)
* [ ] README: instructions about manually generating embeddings
* [ ] README: instructions about using the trait
* [ ] README: add instructions about using this Artisan command during installation "similar-content:install" 
* [ ] throw exception api key missing
* [ ] move api request to own class
* [ ] use this new class to manually call generate SimilarData::generate($article)

### 📝 Optional / Planned

**🔁 Core Package Enhancements**
* [ ] Config flag to enable/disable automatic embedding generation on model `save()` (default: false)
* [ ] Store embedding type (e.g., `search`, `similarity`, etc.) alongside embedding
* [ ] `EmbeddingContext::generate()` method
* [ ] Support for multiple embedding types (search vs similarity)
* [ ] Support for transformers
* [ ] Per-user personalization
* [ ] Configurable model paths
* [ ] Register models via class or factory
* [ ] In-depth caching
* [ ] Add event dispatching for lifecycle hooks

**🧪 Test & Dev Utilities**
* [ ] Add `SimilarContent::fake()` method
* [ ] Prevent real API calls (no OpenAI usage)
* [ ] Return predictable stub embeddings*
* [ ] Support `SimilarContent::assertEmbeddingGeneratedFor($model)`
* [ ] Support `SimilarContent::assertNothingGenerated()`
* [ ] Allow fake responses for `findSimilar()`
* [ ] Document or auto-enable in `testing`/`local` environments
