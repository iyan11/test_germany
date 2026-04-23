<?php

declare(strict_types=1);

namespace App\Support;

final class Env
{
    public static function getString(string $key, string $default = ''): string
    {
        $value = self::raw($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }

    public static function getFloat(string $key, float $default): float
    {
        $value = self::raw($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return (float) $value;
    }

    private static function raw(string $key): string|null
    {
        if (array_key_exists($key, $_ENV)) {
            return (string) $_ENV[$key];
        }
        if (array_key_exists($key, $_SERVER)) {
            return (string) $_SERVER[$key];
        }

        $value = getenv($key);
        return $value === false ? null : (string) $value;
    }
}
