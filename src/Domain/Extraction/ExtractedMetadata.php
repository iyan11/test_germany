<?php

declare(strict_types=1);

namespace App\Domain\Extraction;

final readonly class ExtractedMetadata
{
    /**
     * @param array<int, string> $modelNumbers
     * @param array<int, string> $compatibleBrands
     */
    public function __construct(
        public string $productType,
        public ?string $material,
        public ?string $color,
        public ?string $deviceCategoryHint,
        public array $modelNumbers,
        public array $compatibleBrands,
    ) {
    }

    public function primaryModelNumber(): ?string
    {
        return $this->modelNumbers[0] ?? null;
    }

    /** @return array<int, string> */
    public function compatibleModelNumbers(int $limit = 3): array
    {
        return array_slice($this->modelNumbers, 0, $limit);
    }
}
