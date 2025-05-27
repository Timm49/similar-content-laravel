<?php

namespace Timm49\SimilarContentLaravel;

use Illuminate\Database\Eloquent\Model;
use Timm49\SimilarContentLaravel\Attributes\HasEmbeddings;
use Timm49\SimilarContentLaravel\SimilarContentContext;

class SimilarContent
{
    private static array $registeredModels = [];

    public static function for(Model $model): SimilarContentContext
    {
        return new SimilarContentContext($model);
    }

    public static function getRegisteredModels(?string $path = null): array
    {
        self::$registeredModels = [];
        $path ??= config('similar_content.models_path', app_path('Models'));
        
        foreach (glob($path . '/*.php') as $file) {
            $className = self::extractNamespaceFromFile($file) . '\\' . basename($file, '.php');
            
            if (! class_exists($className)) {
                continue;
            }

            if (! is_subclass_of($className, Model::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);
            $attributes = $reflection->getAttributes(HasEmbeddings::class);

            if (! empty($attributes)) {
                self::$registeredModels[] = $className;
            }
        }

        return self::$registeredModels;
    }

    static function extractNamespaceFromFile($filePath): ?string {
        $fileContents = file_get_contents($filePath);
        $namespacePattern = '/namespace\s+([^;]+);/';

        if (preg_match($namespacePattern, $fileContents, $matches)) {
            return $matches[1];
        }

        return null;
    }
}