<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key
    |--------------------------------------------------------------------------
    |
    | This is the API key used for generating embeddings. If not set, it will
    | fall back to the default OpenAI API key from your .env file.
    |
    */
    'openai_api_key' => env('SIMILAR_CONTENT_OPENAI_API_KEY'),
    /*
    |--------------------------------------------------------------------------
    | Auto Generate (use wisely!)
    |--------------------------------------------------------------------------
    |
    | When set to true, the package will automatically generate embeddings for
    | the models with the #[HasEmbeddings] attribute assigned. It listens to
    | Laravels saved() model event.
    |
    */
    'auto_generate' => false,
];