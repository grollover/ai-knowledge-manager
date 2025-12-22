<?php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class VectorType extends Type
{
    public const VECTOR = 'vector';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $dimensions = $column['options']['dimensions'] ?? 1536;
        return "vector($dimensions)";
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (is_array($value)) {
            return '[' . implode(',', $value) . ']';
        }
        return $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (is_string($value)) {
            return array_map('floatval', explode(',', trim($value, '[]')));
        }
        return $value;
    }

    public function getName(): string
    {
        return self::VECTOR;
    }
}
