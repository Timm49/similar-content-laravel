<?php

return [
    'openai_api_key' => env('SIMILAR_CONTENT_OPENAI_API_KEY'), // OpenAI API Key
    'auto_generate' => false, // auto generate embeddings when a model is created/updated
    'cache_enabled' => false, // cache the similar content results
    'cache_ttl' => 3600,   // default ttl in seconds
];