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
                $models[] = $className;
            }
        }

        return $models;
    }

    static function extractNamespaceFromFile($filePath) {
        // Read the file contents
        $fileContents = file_get_contents($filePath);

        // Define the regular expression pattern to match the namespace
        $namespacePattern = '/namespace\s+([^;]+);/';

        // Use preg_match to find the namespace
        if (preg_match($namespacePattern, $fileContents, $matches)) {
            // Return the captured namespace
            return $matches[1];
        }

        // Return null if no namespace is found
        return null;
    }
}