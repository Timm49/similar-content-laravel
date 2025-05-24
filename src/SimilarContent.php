<?php

namespace Timm49\LaravelSimilarContent;

use App\Interfaces\ModelEmbed;
use Illuminate\Database\Eloquent\Model;
use Timm49\LaravelSimilarContent\Attributes\HasSimilarContent;
use Timm49\LaravelSimilarContent\ValueObjects\EmbedModal;

class SimilarContent
{
    private static array $registeredModels = [];

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
            $attributes = $reflection->getAttributes(HasSimilarContent::class);

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