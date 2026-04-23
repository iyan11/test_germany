<?php

declare(strict_types=1);

namespace App\Support;

final class ArrayUtils
{
    /**
     * @param array<int, string> $values
     * @return array<int, string>
     */
    public static function uniquePreserveOrder(array $values): array
    {
        $seen = [];
        $result = [];

        foreach ($values as $value) {
            $key = mb_strtolower($value);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $result[] = $value;
        }

        return $result;
    }
}
