<?php

declare(strict_types=1);

namespace App\Service;

final class DeviceCategoryHintExtractor
{
    /** @var array<string, string> */
    private array $rules = [
        'backofen' => 'Backofen / Herd',
        'herd' => 'Backofen / Herd',
        'waschmaschine' => 'Waschmaschine / Trockner',
        'trockner' => 'Waschmaschine / Trockner',
        'kühlschrank' => 'Kühlschrank / Gefriergerät',
        'gefrier' => 'Kühlschrank / Gefriergerät',
        'geschirrspüler' => 'Geschirrspüler',
        'kaffeeautomat' => 'Kaffeemaschine / Kaffeeautomat',
        'kaffeemaschine' => 'Kaffeemaschine / Kaffeeautomat',
        'dunstabzugshaube' => 'Dunstabzugshaube',
        'staubsauger' => 'Staubsauger',
        'fleischwolf' => 'Fleischwolf',
        'küchenmaschine' => 'Küchenmaschine',
    ];

    public function extract(string $title): ?string
    {
        $lower = mb_strtolower($title);
        foreach ($this->rules as $keyword => $hint) {
            if (mb_strpos($lower, $keyword) !== false) {
                return $hint;
            }
        }

        return null;
    }
}
