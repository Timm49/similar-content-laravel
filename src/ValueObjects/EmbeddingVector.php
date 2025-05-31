<?php

namespace Timm49\SimilarContentLaravel\ValueObjects;

readonly class EmbeddingVector
{
    public const DIMENSION = 1536;

    /** @var float[] */
    public array $values;

    public function __construct(array $values)
    {
        if (count($values) !== self::DIMENSION) {
            throw new \InvalidArgumentException('Embedding must have exactly ' . self::DIMENSION . ' dimensions.');
        }

        foreach ($values as $value) {
            if (!is_float($value) && !is_int($value)) {
                throw new \InvalidArgumentException('Each embedding value must be a float or int.');
            }
        }

        // Normalize to floats
        $this->values = array_map(fn($v) => (float) $v, $values);
    }

    public function toArray(): array
    {
        return $this->values;
    }

    public function toJson(): string
    {
        return json_encode($this->values);
    }
}