### ✅ Done
* [x] Use `#[HasSimilarContent]` to mark models for embedding generation
* [x] Publish migrations
* [x] Publish configs
* [x] Generating embeddings (via command)
* [x] Include a trait which developers can use to overwrite the default `getEmbeddingData()` logic

### 🚨 Urgent Setup
* [ ] Add `SIMILAR_CONTENT_OPENAI_API_KEY` to config to allow a separate OpenAI key from the app
* [ ] Add `php artisan similar-content:install` command to publish config/migrations and show helpful setup reminders
  
### 📝 To Do (Optional / Planned)
**🔁 Core Package Enhancements**
* [ ] Config flag to enable/disable automatic embedding generation on model `save()` (default: false)
* [ ] Retrieve similar content
* [ ] Add `SimilarContentResult` value object for similarity results
* [ ] Store embedding type (e.g., `search`, `similarity`, etc.) alongside embedding
* [ ] Chain `SimilarContent::for($model)` to return an `EmbeddingContext`
* [ ] `EmbeddingContext::generate()` method
* [ ] `EmbeddingContext::findSimilar()` method

**🧪 Test & Dev Utilities**
* [ ] Add `SimilarContent::fake()` method
* [ ] Prevent real API calls (no OpenAI usage)
* [ ] Return predictable stub embeddings*
* [ ] Support `SimilarContent::assertEmbeddingGeneratedFor($model)`
* [ ] Support `SimilarContent::assertNothingGenerated()`
* [ ] Allow fake responses for `findSimilar()`
* [ ] Document or auto-enable in `testing`/`local` environments

**🚧 Future Features**
* [ ] Support for multiple embedding types (search vs similarity)
* [ ] Support for transformers
* [ ] Per-user personalization
* [ ] Configurable model paths
* [ ] Admin UI (optional interface or plugin)
* [ ] In-depth caching
* [ ] Add event dispatching for lifecycle hooks