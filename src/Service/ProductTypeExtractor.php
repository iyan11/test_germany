<?php

declare(strict_types=1);

namespace App\Service;

final class ProductTypeExtractor
{
    /** @var array<string, string> */
    private array $rules = [
        'aufbewahrungstasche' => 'Aufbewahrungstasche',
        'heizung' => 'Heizung',
        'seitenteil' => 'Seitenteil',
        'spannrolle' => 'Spannrolle',
        'feder' => 'Feder',
        'flüssigwaschmitteleinsatz' => 'Flüssigwaschmitteleinsatz',
        'türinnengitter' => 'Türinnengitter',
        'filterbeutel' => 'Filterbeutel',
        'flusensieb' => 'Flusensieb',
        'reedplatine' => 'Reedplatine',
        'knethaken' => 'Knethaken',
        'seilzugführung' => 'Seilzugführung',
        'sprüharm' => 'Sprüharm',
        'heißwasserdüse' => 'Heißwasserdüse',
        'hepa-filter' => 'HEPA-Filter',
        'türhaken' => 'Türhaken',
        'lampenabdeckung' => 'Lampenabdeckung',
        'zahnrad' => 'Zahnrad',
    ];

    public function extract(string $title): string
    {
        $lower = mb_strtolower($title);
        foreach ($this->rules as $keyword => $type) {
            if (mb_strpos($lower, $keyword) !== false) {
                return $type;
            }
        }

        return 'Ersatzteil';
    }
}
