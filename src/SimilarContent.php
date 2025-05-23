<?php

namespace Timm49\LaravelSimilarContent;

use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Attributes\HasSimilarContent;

class SimilarContent
{
    public static function discoverModelsWithEmbeddings(string $path): array
    {
        $models = [];
        $files = glob($path . '/*.php');

        foreach ($files as $file) {
            $className = self::extractNamespaceFromFile($file) . '\\' . basename($file, '.php');
            
            if (! class_exists($className)) {
                continue;
            }

            if (! is_subclass_of($className, Model::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);
            $attributes = $reflection->getAttributes(HasSimilarContent::class);

            if (! empty($attributes)) {
                $models[] = $className;
            }
        }

        return $models;
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