<?php

declare(strict_types=1);

namespace App\Support;

final class StringUtils
{
    public static function normalizeSpaces(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    public static function containsIgnoreCase(string $haystack, string $needle): bool
    {
        return mb_stripos($haystack, $needle) !== false;
    }
}
