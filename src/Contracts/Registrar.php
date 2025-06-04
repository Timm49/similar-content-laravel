<?php

namespace Timm49\SimilarContentLaravel\Contracts;

interface Registrar
{
    public function transform(array $results): array;

    public function getModel(): string;
}