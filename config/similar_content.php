<?php

return [
    'openai_api_key' => env('SIMILAR_CONTENT_OPENAI_API_KEY'), // OpenAI API Key
    'limit_similar_results' => 10, // how many items will be fetched for similar results
    'limit_search_results' => 10, // how many items will be fetched for search results
    'auto_generate' => false, // auto generate embeddings when a model is created/updated
    'cache_enabled' => false, // cache the similar content results
    'cache_ttl' => 3600,   // default ttl in seconds
];