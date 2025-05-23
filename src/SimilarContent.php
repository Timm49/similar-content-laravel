<?php

namespace Timm49\LaravelSimilarContent;

use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Attributes\HasEmbeddings;

class SimilarContent
{
    public static function discoverModelsWithEmbeddings(string $path): array
    {
        $models = [];
        $files = glob($path . '/*.php');

        foreach ($files as $file) {
            $className = 'Timm49\\LaravelSimilarContent\\Tests\\Fixtures\\Models\\' . basename($file, '.php');
            
            if (! class_exists($className)) {
                continue;
            }

            if (! is_subclass_of($className, Model::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);
            $attributes = $reflection->getAttributes(HasEmbeddings::class);

            if (! empty($attributes)) {
                $models[] = $className;
            }
        }

        return $models;
    }
}