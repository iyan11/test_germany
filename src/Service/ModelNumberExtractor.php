<?php

declare(strict_types=1);

namespace App\Service;

use App\Support\ArrayUtils;

final class ModelNumberExtractor
{
    /** @return array<int, string> */
    public function extractAll(string $title): array
    {
        preg_match_all('/\b(?:\d{6,14}|[A-Z]{1,5}\d{1,4}[-\/]\d{1,6}[A-Z]?|\d{1,3}(?:\.\d{2,6}){1,3}|\d\.\d{3}-\d{3}\.\d|[A-Z]{1,4}\d{2,6}-\d{2,6}[A-Z]?)\b/u', mb_strtoupper($title), $matches);
        $numbers = $matches[0] ?? [];

        $filtered = array_values(array_filter($numbers, static function (string $candidate): bool {
            return preg_match('/\d/u', $candidate) === 1 && mb_strlen($candidate) >= 6;
        }));

        return ArrayUtils::uniquePreserveOrder($filtered);
    }

    public function extractPrimary(string $title): ?string
    {
        return $this->extractAll($title)[0] ?? null;
    }

    /** @return array<int, string> */
    public function extractCompatible(string $title, int $limit = 3): array
    {
        return array_slice($this->extractAll($title), 0, $limit);
    }
}
