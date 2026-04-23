<?php

declare(strict_types=1);

namespace App\Service;

use App\Support\StringUtils;

final class TitleFormatter
{
    public function format(string $title, int $maxLength = 80): string
    {
        $title = StringUtils::normalizeSpaces($title);
        if (mb_strlen($title) <= $maxLength) {
            return $title;
        }

        $truncated = mb_substr($title, 0, $maxLength);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace > (int) ($maxLength * 0.6)) {
            return rtrim(mb_substr($truncated, 0, $lastSpace));
        }

        return rtrim($truncated);
    }
}
