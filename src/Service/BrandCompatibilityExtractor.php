<?php

declare(strict_types=1);

namespace App\Service;

use App\Support\ArrayUtils;

final class BrandCompatibilityExtractor
{
    /** @var array<int, string> */
    private array $brands = [
        'Bosch', 'Siemens', 'Samsung', 'Whirlpool', 'Beko', 'Liebherr', 'Kärcher', 'Philips',
        'Electrolux', 'AEG', 'Ignis', 'Bauknecht', 'Nilfisk', 'Ariete', 'Gaggia', 'DeLonghi', 'WMF',
    ];

    /** @return array<int, string> */
    public function extract(string $title): array
    {
        $found = [];
        foreach ($this->brands as $brand) {
            if (preg_match('/\b' . preg_quote($brand, '/') . '\b/ui', $title) === 1) {
                $found[] = $brand;
            }
        }

        return ArrayUtils::uniquePreserveOrder($found);
    }
}
