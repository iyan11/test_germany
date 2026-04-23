<?php

declare(strict_types=1);

namespace App\Service;

final class ColorExtractor
{
    /** @var array<int, string> */
    private array $colors = ['weiß', 'schwarz', 'grau', 'silber', 'blau', 'rot', 'grün', 'transparent', 'beige', 'braun'];

    public function extract(string $title): ?string
    {
        $lower = mb_strtolower($title);
        foreach ($this->colors as $color) {
            if (mb_strpos($lower, $color) !== false) {
                return $color;
            }
        }

        return null;
    }
}
