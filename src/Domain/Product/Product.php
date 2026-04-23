<?php

declare(strict_types=1);

namespace App\Domain\Product;

final readonly class Product
{
    public function __construct(
        public int $id,
        public string $extId,
        public string $title,
        public string $condition,
        public ?int $manufacturerId,
        public ?float $purchasePrice,
        public ?string $image,
    ) {
    }
}
