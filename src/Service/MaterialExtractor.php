<?php

declare(strict_types=1);

namespace App\Service;

final class MaterialExtractor
{
    /** @var array<int, string> */
    private array $materials = ['Metall', 'Kunststoff', 'Gummi', 'Glas', 'Edelstahl', 'Aluminium'];

    public function extract(string $title): ?string
    {
        foreach ($this->materials as $material) {
            if (preg_match('/\b' . preg_quote($material, '/') . '\b/ui', $title) === 1) {
                return $material;
            }
        }

        return null;
    }
}
